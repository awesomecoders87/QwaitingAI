<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Counter;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QueueCsvImporter
{
    protected int $created = 0;
    protected string $type = "psb";
    protected int $locationsId = 0;
    protected ?int $teamId = null;
    protected int $processed = 0;
    protected int $failed = 0;
    protected array $failedRows = [];

    public function importFromUploadedFile(UploadedFile $file, int $locationsId = 0, ?int $teamId = null, int $chunk = 1000,$type="psb"): array
    {

        $this->locationsId = $locationsId;
        $this->teamId = tenant('id');
        $this->type = $type;
       
        $path = $file->getRealPath();
        if (!$path || !file_exists($path)) {
            return ['ok' => false, 'created' => 0, 'message' => 'File not accessible'];
        }

        $fh = fopen($path, 'r');
        if (!$fh) {
            return ['ok' => false, 'created' => 0, 'message' => 'Cannot open file'];
        }

        $firstLine = '';
        while (($line = fgets($fh)) !== false) {
            if (trim($line) !== '') { $firstLine = $line; break; }
        }
        if ($firstLine === '') { fclose($fh); return ['ok' => true, 'created' => 0, 'message' => 'Empty CSV']; }
        $delimiter = $this->guessDelimiter($firstLine);
        rewind($fh);

        // Read until we find a likely header row
        $expected = [
            'token','destination1','destination','service','level 1','airlines','level 2','flight number','level 3','flightnumber','counter','name','customer','contact','email','date & time','datetime','closed by','closedby','status','descriptions','description'
        ];
        $header = false;
        while (($candidate = fgetcsv($fh, 0, $delimiter)) !== false) {
            $trimmed = array_map(fn($v) => trim((string)$v), $candidate);
            if (count(array_filter($trimmed, fn($v) => $v !== '')) < 2) {
                // too few cells with content, keep scanning
                continue;
            }
            $lower = array_map('strtolower', $trimmed);
            $match = false;
            foreach ($expected as $e) {
                if (in_array($e, $lower, true)) { $match = true; break; }
            }
            if ($match) { $header = $trimmed; break; }
        }
        if ($header === false) { fclose($fh); return ['ok' => false, 'created' => 0, 'message' => 'No valid header found']; }

        $batch = [];
        while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
            if ($row === null || (count($row) === 1 && trim((string)$row[0]) === '')) { continue; }
            $batch[] = $this->associateRow($header, $row);

            if (count($batch) >= $chunk) { $this->processRows($batch); $batch = []; }
        }
        if (!empty($batch)) { $this->processRows($batch); }
        fclose($fh);

        $result = [
            'ok' => true,
            'created' => $this->created,
            'processed' => $this->processed,
            'failed' => $this->failed,
            'message' => 'Imported',
        ];
        if (!empty($this->failedRows)) {
            $result['errors'] = array_slice($this->failedRows, 0, 100);
        }
        return $result;
    }

    protected function guessDelimiter(string $line): string
    {
        $candidates = [",", ";", "\t"]; $best = ","; $max = -1;
        foreach ($candidates as $d) { $c = substr_count($line, $d); if ($c > $max) { $max = $c; $best = $d; } }
        return $best;
    }

    protected function associateRow(array $header, array $row): array
    {
        $out = [];
        foreach ($header as $i => $key) {
            // Normalize header key: trim + strip BOM + lowercase alias
            $norm = trim($key);
            $norm = preg_replace("/^\xEF\xBB\xBF/", '', $norm) ?: $norm; // strip UTF-8 BOM if present
            $value = $row[$i] ?? '';
            $out[$norm] = $value;
            $out[strtolower($norm)] = $value; // add lowercase alias to make lookups robust
        }
        return $out;
    }

    protected function processRows(array $rows): void
    {
        $this->processed += count($rows);

        // 1) Pre-collect uniques for lookups
        $teamId = $this->teamId ?? 3;
        $uniques = [
            'users' => [], 'c1' => [], 'c2' => [], 'c3' => [], 'counters' => [],
            // parent maps to help set parent_id on level 2/3
            'c2p' => [], // airline => destination1
            'c3p' => [], // flight => airline
        ];
        
        // Store category mappings for PSB type
        $categoryMappings = [];
        
        // Function to clean and extract category name from PSB format
        $extractCategoryName = function($input) {
            // Remove HTML tags and everything after
            $cleaned = preg_replace('/<[^>]*>.*$/', '', $input);
            // Trim any whitespace
            $cleaned = trim($cleaned);
            // Remove any trailing dashes or arrows
            return trim($cleaned, " ->");
        };
        
        // Function to clean text by removing HTML tags and trimming
        $cleanText = function($input) {
            // Convert HTML entities to their corresponding characters
            $input = html_entity_decode($input);
            // Remove all HTML tags
            $input = strip_tags($input);
            // Trim whitespace and clean up any remaining special characters
            return trim(preg_replace('/\s+/', ' ', $input));
        };
        
        foreach ($rows as $r) {
            $dest1 = $this->val($r, ['Level 1','level 1','Destination1','Destination','Service','destination1','destination','service']);
            $air   = $this->val($r, ['Level 2','level 2','Airlines','airlines']);
            $fl    = $this->val($r, ['Level 3','level 3','Flight Number','FlightNumber','flight number','flightnumber']);
            $ctr   = $this->val($r, ['Counter','counter']);
            $cby   = $this->val($r, ['Closed By','ClosedBy','closed by','closed_by','closedby']);
            $called = strtolower($this->val($r, ['Called', 'called']) ?? 'no');
            
            // Handle PSB type specific parsing for category and subcategory
            if ($this->type === 'psb' && !empty($dest1)) {
                // Extract category and subcategory from level 1 field
                $categoryParts = [];
                if (strpos($dest1, '->') !== false) {
                    $categoryParts = explode('->', $dest1);
                    $categoryParts = array_map('trim', $categoryParts);
                    
                    // First part is always the service type (skip it)
                    // Second part is the category (e.g., School of Health and Life Sciences)
                    if (count($categoryParts) > 1) {
                        $category = $extractCategoryName($categoryParts[1]);
                        
                        // Check if there's a third part (subcategory)
                        if (count($categoryParts) > 2) {
                            // Extract and clean the subcategory text (remove HTML tags and trim)
                            $subcategoryText = trim(strip_tags($categoryParts[2]));
                            
                            // Store mappings with cleaned subcategory
                            $categoryMappings[$dest1] = [
                                'category' => $category,
                                'subcategory' => $subcategoryText,
                                'childcategory' => null
                            ];
                            
                            $uniques['c1'][$category] = true;
                            $uniques['c2'][$subcategoryText] = true;
                            
                            // Set parent relationships with cleaned subcategory
                            $uniques['c2p'][$subcategoryText] = $category;
                        } else {
                            // Only category, no subcategory
                            $categoryMappings[$dest1] = [
                                'category' => $category,
                                'subcategory' => null,
                                'childcategory' => null
                            ];
                            
                            $uniques['c1'][$category] = true;
                        }
                    }
                } else {
                    // No -> found, treat as category only
                    $category = $extractCategoryName($dest1);
                    $categoryMappings[$dest1] = [
                        'category' => $category,
                        'subcategory' => null,
                        'childcategory' => null
                    ];
                    $uniques['c1'][$category] = true;
                }
            } else {
                // Original behavior for non-PSB type
                if ($dest1) $uniques['c1'][$dest1] = true;
                if ($air)   $uniques['c2'][$air] = true;
                if ($fl)    $uniques['c3'][$fl] = true;
                
                // capture parent mapping for level 2 and 3
                if ($air && $dest1 && !isset($uniques['c2p'][$air])) { 
                    $uniques['c2p'][$air] = $dest1; 
                }
                if ($fl && $air && !isset($uniques['c3p'][$fl])) { 
                    $uniques['c3p'][$fl] = $air; 
                }
            }
            
            // Common fields for both types
            if ($ctr)   $uniques['counters'][$ctr] = true;
            if ($cby && $this->isValidUserName($cby)) $uniques['users'][$cby] = true;
        }

        // 2) Load existing ids
        $ids = [
            'users' => $this->loadExisting('users', 'name', array_keys($uniques['users']), ['team_id' => $teamId]),
            'c1' => $this->loadExisting('categories', 'name', array_keys($uniques['c1']), ['team_id' => $teamId, 'level_id' => 1]),
            'c2' => $this->loadExisting('categories', 'name', array_keys($uniques['c2']), ['team_id' => $teamId, 'level_id' => 2]),
            'c3' => $this->loadExisting('categories', 'name', array_keys($uniques['c3']), ['team_id' => $teamId, 'level_id' => 3]),
            'counters' => $this->loadExisting('counters', 'name', array_keys($uniques['counters']), ['team_id' => $teamId]),
        ];

        // 3) Insert missing in bulk (ignore duplicates)
        try {
            if (!empty($uniques['users'])) {
                $toInsert = [];
                foreach (array_keys($uniques['users']) as $name) {
                    if (!isset($ids['users'][$name])) {
                        $toInsert[] = [
                            'name' => $name,
                            'team_id' => $teamId,
                            'username' => $this->uniqueUsername($name),
                            'password' => bcrypt('Password@123'),
                            'locations' => json_encode($this->locationsId ? [(string)$this->locationsId] : []),
                            'created_at' => now(), 'updated_at' => now(),
                        ];
                    }
                }
                if ($toInsert) DB::table('users')->insertOrIgnore($toInsert);
            }

            // Insert level 1 (no parent), then refresh ids
            $toInsert = [];
            foreach (array_keys($uniques['c1']) as $name) {
                if (!isset($ids['c1'][$name])) {
                    $toInsert[] = [
                        'name' => $name,
                        'team_id' => $teamId,
                        'level_id' => 1,
                        'category_locations' => json_encode($this->locationsId ? [(string)$this->locationsId] : []),
                        'created_at' => now(), 'updated_at' => now(),
                    ];
                }
            }
            if ($toInsert) DB::table('categories')->insertOrIgnore($toInsert);

            // Refresh level 1 ids after insert
            $ids['c1'] += $this->loadExisting('categories', 'name', array_keys($uniques['c1']), ['team_id' => $teamId, 'level_id' => 1]);

            // Insert level 2 with parent_id using mapping to level 1
            $toInsert = [];
            foreach (array_keys($uniques['c2']) as $name) {
                if (!isset($ids['c2'][$name])) {
                    $rowArr = [
                        'name' => $name,
                        'team_id' => $teamId,
                        'level_id' => 2,
                        'category_locations' => json_encode($this->locationsId ? [(string)$this->locationsId] : []),
                        'created_at' => now(), 'updated_at' => now(),
                    ];
                    $parentName = $uniques['c2p'][$name] ?? null;
                    if ($parentName && isset($ids['c1'][$parentName])) {
                        $rowArr['parent_id'] = $ids['c1'][$parentName];
                    }
                    $toInsert[] = $rowArr;
                }
            }
            if ($toInsert) DB::table('categories')->insertOrIgnore($toInsert);

            // Refresh level 2 ids after insert
            $ids['c2'] += $this->loadExisting('categories', 'name', array_keys($uniques['c2']), ['team_id' => $teamId, 'level_id' => 2]);

            // Insert level 3 with optional parent to level 2 if available
            $toInsert = [];
            foreach (array_keys($uniques['c3']) as $name) {
                if (!isset($ids['c3'][$name])) {
                    $rowArr = [
                        'name' => $name,
                        'team_id' => $teamId,
                        'level_id' => 3,
                        'category_locations' => json_encode($this->locationsId ? [(string)$this->locationsId] : []),
                        'created_at' => now(), 'updated_at' => now(),
                    ];
                    // If we know the airline parent for this flight and its id exists, set as parent
                    $parentAir = $uniques['c3p'][$name] ?? null;
                    if ($parentAir && isset($ids['c2'][$parentAir])) {
                        $rowArr['parent_id'] = $ids['c2'][$parentAir];
                    }
                    $toInsert[] = $rowArr;
                }
            }
            if ($toInsert) DB::table('categories')->insertOrIgnore($toInsert);

            if (!empty($uniques['counters'])) {
                $toInsert = [];
                foreach (array_keys($uniques['counters']) as $name) {
                    if (!isset($ids['counters'][$name])) {
                        $toInsert[] = [
                            'name' => $name,
                            'team_id' => $teamId,
                            'show_checkbox' => 1,
                            'counter_locations' => json_encode($this->locationsId ? [(string)$this->locationsId] : []),
                            'created_at' => now(), 'updated_at' => now(),
                        ];
                    }
                }
                if ($toInsert) DB::table('counters')->insertOrIgnore($toInsert);
            }
        } catch (\Throwable $e) {
            Log::error('Bulk pre-insert failed', ['e' => $e->getMessage()]);
        }

        // 4) Refresh id maps
        $ids['users'] += $this->loadExisting('users', 'name', array_keys($uniques['users']), ['team_id' => $teamId]);
        
        // Load category IDs, ensuring we have the latest mappings
        $ids['c1'] = $this->loadExisting('categories', 'name', array_keys($uniques['c1']), ['team_id' => $teamId, 'level_id' => 1]);
        $ids['c2'] = $this->loadExisting('categories', 'name', array_keys($uniques['c2']), ['team_id' => $teamId, 'level_id' => 2]);
        $ids['c3'] = $this->loadExisting('categories', 'name', array_keys($uniques['c3']), ['team_id' => $teamId, 'level_id' => 3]);
        
        // For PSB type, ensure we have all the mapped categories created
        if ($this->type === 'psb') {
            foreach ($categoryMappings as $mapping) {
                if (!empty($mapping['category']) && !isset($ids['c1'][$mapping['category']])) {
                    $ids['c1'][$mapping['category']] = $this->firstOrCreateCategory($teamId, 1, null, $mapping['category']);
                }
                if (!empty($mapping['subcategory']) && !empty($mapping['category']) && !isset($ids['c2'][$mapping['subcategory']])) {
                    $parentId = $ids['c1'][$mapping['category']] ?? null;
                    $ids['c2'][$mapping['subcategory']] = $this->firstOrCreateCategory($teamId, 2, $parentId, $mapping['subcategory']);
                }
                if (!empty($mapping['childcategory']) && !empty($mapping['subcategory']) && !isset($ids['c3'][$mapping['childcategory']])) {
                    $parentId = $ids['c2'][$mapping['subcategory']] ?? null;
                    $ids['c3'][$mapping['childcategory']] = $this->firstOrCreateCategory($teamId, 3, $parentId, $mapping['childcategory']);
                }
            }
        }
        
        $ids['counters'] += $this->loadExisting('counters', 'name', array_keys($uniques['counters']), ['team_id' => $teamId]);

        // 5) Build bulk queue + storage rows
        $queueTable = (new Queue())->getTable();
        $qsTable = (new QueueStorage())->getTable();
        $now = now();

        $queueRows = [];
        $storageRows = [];
        $tokensForLookup = [];
        $rowBuffer = [];

        foreach ($rows as $row) {
            try {
                $tokenRaw = $this->val($row, ['Token','token']);
                $tokenNum = $tokenRaw ? preg_replace('/\\D+/', '', $tokenRaw) : null;
                $tokenAcr = $tokenRaw ? preg_replace('/\\d+/', '', $tokenRaw) : null;

                $dest1 = $this->val($row, ['Level 1','level 1','Destination1','Destination','Service','destination1','destination','service']);
                $air   = $this->val($row, ['Level 2','level 2','Airlines','airlines']);
                $fl    = $this->val($row, ['Level 3','level 3','Flight Number','FlightNumber','flight number','flightnumber']);
                $ctr   = $this->val($row, ['Counter','counter']);
                $name  = $this->val($row, ['Name','Customer','name','customer']);
                $phone = $this->val($row, ['Contact','phone','contact']);
                $email = $this->val($row, ['Email','email']);
                $desc  = $this->val($row, ['Descriptions','Description','descriptions','description','Alfursan','alfursan']);
                $rating= $this->val($row, ['Rating','rating']);
                $arr = $this->val($row, ['Date & Time','date & time','datetime','date_time']);
                if ($arr) {
                    $originalValue = $arr; // Store original for error reporting
                    try {
                        // First try m/d/Y format (7/11/2025 9:10:00 AM)
                        $date = Carbon::createFromFormat('n/j/Y h:i:s A', $arr);
                        if ($date !== false) {
                            $arr = $date->format('Y-m-d H:i:s');
                            \Log::info("Successfully parsed date (m/d/Y format): {$originalValue} -> {$arr}");
                        } else {
                            // Try with single digit month/day 'n/j/Y h:i:s A' (7/11/2025 9:10:00 AM)
                            $date = Carbon::createFromFormat('n/j/Y h:i:s A', $arr);
                            if ($date !== false) {
                                $arr = $date->format('Y-m-d H:i:s');
                                \Log::info("Successfully parsed date (n/j/Y format): {$originalValue} -> {$arr}");
                            } else {
                                // Fallback to d/m/Y format for other date formats
                                $date = Carbon::createFromFormat('d/m/Y h:i:s A', $arr);
                                if ($date !== false) {
                                    $arr = $date->format('Y-m-d H:i:s');
                                    \Log::info("Successfully parsed date (d/m/Y format): {$originalValue} -> {$arr}");
                                } else {
                                    // If all formats fail, try Carbon's built-in parser as last resort
                                    try {
                                        $date = Carbon::parse($arr);
                                        $arr = $date->format('Y-m-d H:i:s');
                                        \Log::info("Successfully parsed date (Carbon parse): {$originalValue} -> {$arr}");
                                    } catch (\Exception $e) {
                                        $error = "Failed to parse date: '{$originalValue}'. Error: " . $e->getMessage();
                                        \Log::error($error);
                                        // Keep original value if all parsing fails
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $error = "Unexpected error parsing date '{$originalValue}': " . $e->getMessage();
                        \Log::error($error);
                        // Keep original value if parsing fails
                    }
                }
                $cby   = $this->val($row, ['Closed By','ClosedBy','closed by','closed_by','closedby']);
                $called = strtolower($this->val($row, ['Called', 'called']) ?? 'no');
                
                if ($cby !== null && $cby !== '') { 
                    $cby = str_replace('/', '', $cby);
                    $cby = preg_replace('/\[[^\]]*\]/', '', $cby);
                    $cby = trim($cby);
                }
                
                // For PSB type, map the category and subcategory from level 1
                if ($this->type === 'psb' && !empty($dest1) && !empty($categoryMappings[$dest1])) {
                    $mapping = $categoryMappings[$dest1];
                    
                    // Get the actual category IDs from the names
                    $c1 = $mapping['category'] && isset($ids['c1'][$mapping['category']]) ? $ids['c1'][$mapping['category']] : null;
                    $c2 = $mapping['subcategory'] && isset($ids['c2'][$mapping['subcategory']]) ? $ids['c2'][$mapping['subcategory']] : null;
                    $c3 = $mapping['childcategory'] && isset($ids['c3'][$mapping['childcategory']]) ? $ids['c3'][$mapping['childcategory']] : null;
                }
                $resp  = $this->val($row, [
                    'Response Time (Minutes)','response time (minutes)',
                    'Response Time Minutes','response time minutes',
                    'Response Time','response time','ResponseTime','responsetime'
                ]);
                $serv  = $this->val($row, [
                    'Serving Time (Minutes)','serving time (minutes)',
                    'Serving Time Minutes','serving time minutes',
                    'Serving Time','serving time','ServingTime','servingtime'
                ]);
                $statusRaw = $this->val($row, ['Status','status','Closed Status','closed status']) ?? '';
                $status = strtolower($statusRaw);
                
                // For PSB type, determine status based on response and serving times
                if ($this->type === 'psb') {
                    $respSec = $this->parseTimeToSeconds($resp);
                    $servSec = $this->parseTimeToSeconds($serv);
                    
                    // Default status and called values
                    $status = 'pending';
                    $called = 'no';
                    
                    // If response time is provided (including 0)
                    if ($resp !== null && $resp !== '') {
                        $status = 'progress';
                        $called = 'yes';
                        
                        // Only set to close if serving time has a value (not empty and not just whitespace)
                        if (!empty(trim($serv ?? ''))) {
                            $status = 'close';
                            $called = 'yes';
                        }
                    }
                } 
                // Original behavior for non-PSB type
                elseif ($status === '' || $status === null) {
                    $status = ($cby ? 'close' : 'pending');
                }

                $uId = ($cby && isset($ids['users'][$cby])) ? $ids['users'][$cby] : null;
                
                // For PSB type, use the mapped category names to get the correct IDs
                if ($this->type === 'psb' && !empty($dest1) && !empty($categoryMappings[$dest1])) {
                    $mapping = $categoryMappings[$dest1];
                    $c1 = !empty($mapping['category']) && isset($ids['c1'][$mapping['category']]) ? $ids['c1'][$mapping['category']] : null;
                    $c2 = !empty($mapping['subcategory']) && isset($ids['c2'][$mapping['subcategory']]) ? $ids['c2'][$mapping['subcategory']] : null;
                    $c3 = !empty($mapping['childcategory']) && isset($ids['c3'][$mapping['childcategory']]) ? $ids['c3'][$mapping['childcategory']] : null;
                } else {
                    // Original behavior for non-PSB type
                    $c1 = ($dest1 && isset($ids['c1'][$dest1])) ? $ids['c1'][$dest1] : null;
                    $c2 = ($air && isset($ids['c2'][$air])) ? $ids['c2'][$air] : null;
                    $c3 = ($fl && isset($ids['c3'][$fl])) ? $ids['c3'][$fl] : null;
                }
                $counterId = ($ctr && isset($ids['counters'][$ctr])) ? $ids['counters'][$ctr] : null;

                // Parse time or fallback to now()
                $dt = $now;
                if (!empty($arr)) {
                    try { $dt = Carbon::parse($arr); } catch (\Throwable $e) { $dt = $now; }
                }

                // Build queue row if token exists; otherwise fallback to per-row later
                if ($tokenNum) {
                    $queueRows[] = [
                        'team_id' => $teamId,
                        'status' => Queue::STATUS_PENDING,
                        'locations_id' => $this->locationsId ?: null,
                        'token' => $tokenNum,
                        'start_acronym' => $tokenAcr,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $tokensForLookup[(string)$tokenNum] = true;
                }

                $rowBuffer[] = compact('tokenNum','tokenAcr','name','phone','email','status','dt','uId','c1','c2','c3','counterId','resp','serv','cby','desc','rating','called');
            } catch (\Throwable $e) {
                $this->failed++;
                Log::error('Row build failed', ['e' => $e->getMessage()]);
                if (count($this->failedRows) < 1000) $this->failedRows[] = ['row'=>$row,'error'=>$e->getMessage()];
            }
        }

        // Insert queues in bulk (ignore duplicates by unique indexes)
        if ($queueRows) {
            DB::table($queueTable)->insertOrIgnore($queueRows);
            $this->created += count($queueRows);
        }

        // Map tokens to queue IDs
        $queueIdByToken = [];
        if (!empty($tokensForLookup)) {
            $tokens = array_keys($tokensForLookup);
            $found = DB::table($queueTable)->where('team_id',$teamId)->whereIn('token',$tokens)->get(['id','token']);
            foreach ($found as $f) { $queueIdByToken[(string)$f->token] = (int)$f->id; }
        }

        // Build storage rows and collect rows without token to fallback
        $fallbackRows = [];
        foreach ($rowBuffer as $buf) {
            extract($buf);
            if ($tokenNum && isset($queueIdByToken[(string)$tokenNum])) {
                $jsonPayload = ['name'=>$name,'email'=>$email];
                if (!empty($phone)) { $jsonPayload['phone'] = $phone; }
                if (!empty($desc)) { $jsonPayload['descriptions'] = $desc; }
                if (!empty($rating)) { $jsonPayload['rating'] = $rating; }

                // Map status to queue storage status
                $queueStatus = QueueStorage::STATUS_PENDING;
                if ($status === 'cancelled') {
                    $queueStatus = QueueStorage::STATUS_CANCELLED;
                } elseif (in_array($status, ['served', 'closed', 'close', 'progress']) || $cby) {
                    $queueStatus = QueueStorage::STATUS_CLOSE;
                } elseif ($status === 'in_progress' || $status === 'progress') {
                    $queueStatus = QueueStorage::STATUS_IN_PROGRESS;
                }
                
                $base = [
                    'team_id' => $teamId,
                    'queue_id' => $queueIdByToken[(string)$tokenNum],
                    'name' => $name ?: null,
                    'phone' => $phone ?: null,
                    'json' => json_encode($jsonPayload),
                    'status' => $queueStatus,
                    'locations_id' => $this->locationsId ?: null,
                    'category_id' => $c1,
                    'sub_category_id' => $c2,
                    'child_category_id' => $c3,
                    'counter_id' => $counterId,
                    'token' => $tokenNum,
                    'start_acronym' => $tokenAcr,
                    'arrives_time' => $dt,
                    'datetime' => $dt,
                    'closed_by' => $uId,
                    'served_by' => $uId,
                    'called_datetime' => null,
                    'start_datetime' => null,
                    'closed_datetime' => null,
                    'called' => $called === 'yes' ? 'yes' : 'no',
                    'cancelled_datetime' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                // For PSB type, handle response and serving times differently
                if ($this->type === 'psb') {
                    $respSec = $this->parseTimeToSeconds($resp);
                    $servSec = $this->parseTimeToSeconds($serv);
                    
                    // If response time is provided (including 0), update called datetime
                    if ($resp !== null && $resp !== '') {
                        $calledAt = (clone $dt)->addSeconds($respSec);
                        $base['called_datetime'] = $calledAt;
                        $base['start_datetime'] = $calledAt;
                        $base['called'] = 'yes';
                        
                        // If serving time is provided (including 0), update closed datetime
                        if ($serv !== null && $serv !== '') {
                            $serveStart = $calledAt ?: $dt;
                            $base['closed_datetime'] = (clone $serveStart)->addSeconds($servSec);
                            $base['status'] = QueueStorage::STATUS_CLOSE;
                        }else{
                            $base['status'] = QueueStorage::STATUS_PENDING;
                        }
                    }
                } else {
                    // Original behavior for non-PSB type
                    $respSec = $this->parseTimeToSeconds($resp);
                    $servSec = $this->parseTimeToSeconds($serv);
                    $calledAt = null;
                    
                    if ($respSec > 0) {
                        $calledAt = (clone $dt)->addSeconds($respSec);
                        $base['called_datetime'] = $calledAt;
                        $base['start_datetime'] = $calledAt;
                        $base['called'] = 'yes';
                    }
                    
                    if ($servSec > 0) {
                        $serveStart = $calledAt ?: $dt;
                        $base['closed_datetime'] = (clone $serveStart)->addSeconds($servSec);
                    }
                }
                
                if ($status === 'cancelled') {
                    $base['cancelled_datetime'] = $now;
                    $base['called'] = 'no';
                }
                $storageRows[] = $base;
            } else {
                $fallbackRows[] = $buf; // process via per-row Eloquent to get queue_id
            }
        }

        if ($storageRows) {
            try {
                $inserted = DB::table($qsTable)->insertOrIgnore($storageRows);
                if (empty($inserted)) {
                    Log::warning('queues_storage bulk insert inserted 0 rows', [
                        'attempted' => count($storageRows),
                        'sample' => $storageRows[0] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('queues_storage bulk insert failed', [
                    'error' => $e->getMessage(),
                    'attempted' => count($storageRows),
                    'sample' => $storageRows[0] ?? null,
                ]);
            }
        }

        // Fallback processing for rows without token (no bulk queue_id mapping possible)
        foreach ($fallbackRows as $buf) {
            try {
                // Recreate a row array compatible with importRow by mapping back to original fields as needed
                $r = [
                    'Token' => $buf['tokenAcr'].$buf['tokenNum'],
                    'Name' => $buf['name'],
                    'Contact' => $buf['phone'],
                    'Email' => $buf['email'],
                    'Status' => $buf['status'],
                    'Closed By' => $buf['cby'] ?? null,
                    'Date & Time' => $buf['dt']->toDateTimeString(),
                    'Response Time (Minutes)' => $buf['resp'] ?? null,
                    'Serving Time (Minutes)' => $buf['serv'] ?? null,
                ];
                $this->importRow($r);
            } catch (\Throwable $e) {
                $this->failed++;
                Log::error('Fallback row import failed', ['e' => $e->getMessage()]);
                if (count($this->failedRows) < 1000) $this->failedRows[] = ['row'=>$buf,'error'=>$e->getMessage()];
            }
        }

        // Persist failures if any
        if (!empty($this->failedRows)) {
            $batch = [];
            foreach ($this->failedRows as $idx => $fr) {
                $batch[] = [
                    'team_id' => $this->teamId,
                    'locations_id' => $this->locationsId ?: null,
                    'source' => 'web-upload',
                    'chunk_size' => count($rows),
                    'row_index' => $idx,
                    'error' => $fr['error'] ?? null,
                    'payload' => json_encode($fr['row'] ?? []),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('queue_import_failures')->insert($batch);
            $this->failedRows = [];
        }
    }

    protected function isValidUserName(string $name): bool
    {
        return !preg_match('/[^A-Za-z0-9\s.\'-]/', $name);
    }

    protected function uniqueUsername(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'user';
        $u = $base; $i = 1;
        while (DB::table('users')->where('username', $u)->exists()) { $i++; $u = $base.'_'.$i; }
        return $u;
    }

    protected function loadExisting(string $table, string $col, array $values, array $where = []): array
    {
        if (empty($values)) return [];
        $q = DB::table($table)->select('id', $col);
        foreach ($where as $k=>$v) { $q->where($k, $v); }
        $rows = $q->whereIn($col, $values)->get();
        $map = [];
        foreach ($rows as $r) { $map[$r->{$col}] = (int)$r->id; }
        return $map;
    }

    protected function val(array $row, array $keys): ?string
    {
        foreach ($keys as $k) { if (isset($row[$k])) return trim((string)$row[$k]); }
        return null;
    }

    protected function parseTimeToSeconds($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }
        
        // If it's already a number, treat as minutes
        if (is_numeric($value)) {
            return (int)$value * 60; // Convert minutes to seconds
        }
        
        $str = trim((string)$value);
        if ($str === '') {
            return 0;
        }

        // Handle "X minute(s)" format (e.g., "0 minutes", "1 minute")
        if (preg_match('/^(\d+)\s*minute[s]?$/i', $str, $matches)) {
            return (int)$matches[1] * 60; // Convert minutes to seconds
        }
        
        // Handle "X:YY" format (minutes:seconds)
        if (preg_match('/^(\d+):(\d{2})$/', $str, $matches)) {
            return ((int)$matches[1] * 60) + (int)$matches[2];
        }
        
        // Handle decimal minutes (e.g., "0.5 minutes")
        if (is_numeric($str)) {
            return (int)round((float)$str * 60);
        }
        
        // Handle hh:mm:ss format
        if (preg_match('/^(\d{1,3}):(\d{1,2}):(\d{1,2})$/', $str, $matches)) {
            return ((int)$matches[1] * 3600) + ((int)$matches[2] * 60) + (int)$matches[3];
        }
        // m.s where .s denotes seconds (e.g., 2.9 => 2 minutes 9 seconds). Allow optional unit suffix.
        if (preg_match('/^(\d+)\.(\d+)\s*(min|mins|minute|minutes)?$/', $str, $m)) {
            $min = (int)$m[1];
            $sec = (int)$m[2];
            if ($sec >= 60) { return ($min * 60) + (int)round(((float)('0.' . $m[2])) * 60); }
            return ($min * 60) + $sec;
        }
        // Decimal minutes or bare number
        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*(min|mins|minute|minutes)?$/i', $str, $m)) {
            $minutes = (float)$m[1];
            return (int) round($minutes * 60);
        }
        // Fallback: first number found treated as minutes
        if (preg_match('/([0-9]+(?:\.[0-9]+)?)/', $str, $m)) {
            $minutes = (float)$m[1];
            return (int) round($minutes * 60);
        }
        return 0;
    }

    protected function importRow(array $row): void
    {
        $teamId = $this->teamId ?? 3;
        $token        = trim($row['Token'] ?? $row['token'] ?? '') ?: null;
        $destination1 = trim($row['Destination1'] ?? $row['Destination'] ?? $row['Service'] ?? $row['destination1'] ?? $row['destination'] ?? $row['service'] ?? '');
        $airlines     = trim($row['Airlines'] ?? $row['airlines'] ?? '');
        $flightNumber = trim($row['Flight Number'] ?? $row['FlightNumber'] ?? $row['flight number'] ?? $row['flightnumber'] ?? '');
        $counterName  = trim($row['Counter'] ?? $row['counter'] ?? '');
        $name         = trim($row['Name'] ?? $row['Customer'] ?? $row['name'] ?? $row['customer'] ?? '');
        $phone        = trim($row['Contact'] ?? $row['contact'] ?? '');
        $email        = trim($row['Email'] ?? $row['email'] ?? '');
        $arrives_time = trim($row['Date & Time'] ?? $row['date & time'] ?? $row['datetime'] ?? '');
        $closed_by    = trim(($row['Closed By'] ?? $row['ClosedBy'] ?? $row['closed by'] ?? $row['closed_by'] ?? $row['closedby'] ?? ''));
        if ($closed_by !== '') { 
            $closed_by = str_replace('/', '', $closed_by);
            $closed_by = preg_replace('/\[[^\]]*\]/', '', $closed_by);
            $closed_by = trim($closed_by);
        }
        $responsetime = trim($row['Response Time (Minutes)'] ?? $row['response time (minutes)'] ?? '');
        $servingtime  = trim($row['Serving Time (Minutes)'] ?? $row['serving time (minutes)'] ?? '');
        $descriptions = trim($row['Descriptions'] ?? $row['Description'] ?? $row['descriptions'] ?? $row['description'] ?? $row['Alfursan'] ?? $row['alfursan'] ?? '');
        $rating       = trim($row['Rating'] ?? $row['rating'] ?? '');
        $statusRaw    = trim($row['Status'] ?? $row['status'] ?? $row['Closed Status'] ?? $row['closed status'] ?? '');
        $status       = strtolower($statusRaw);
        if ($status === '' || $status === null) {
            $status = ($closed_by !== '' ? 'close' : 'pending');
        }

        // Proceed even if categories are empty; we'll create queue records without category links

        $level1 = $this->firstOrCreateCategory($teamId, Category::STEP_1, null, $destination1);
        $level2 = $airlines !== '' ? $this->firstOrCreateCategory($teamId, Category::STEP_2, $level1?->id, $airlines) : null;
        $level3 = $flightNumber !== '' ? $this->firstOrCreateCategory($teamId, Category::STEP_3, ($level2?->id ?? $level1?->id), $flightNumber) : null;

        $counter = null;
        if ($counterName !== '') {
            $counter = Counter::firstOrCreate([
                'team_id' => $teamId,
                'name'    => $counterName,
            ], [
                'show_checkbox' => true,
                'counter_locations' => $this->locationsId ? [(string)$this->locationsId] : [],
            ]);
            if ($counter && $this->locationsId) {
                $locs = $counter->counter_locations ?? [];
                if (!is_array($locs)) { $locs = []; }
                $locsStr = array_map('strval', $locs);
                if (!in_array((string)$this->locationsId, $locsStr, true)) {
                    $locs[] = (string)$this->locationsId;
                    $counter->counter_locations = $locs;
                    $counter->save();
                }
            }
        }

        $closedByUserId = null; $user = null; $locsStruser = [];
        if ($closed_by !== '') {
            if (!preg_match('/[^A-Za-z0-9\s.\'-]/', $closed_by)) {
                if (!in_array((string)$this->locationsId, $locsStruser, true)) { $locsStruser[] = (string)$this->locationsId; }
                $baseUsername = Str::slug($closed_by, '_');
                $username = $baseUsername !== '' ? $baseUsername : 'user';
                $j = 1; while (User::where('username', $username)->exists()) { $j++; $username = $baseUsername . '_' . $j; }
                $user = User::firstOrCreate(
                    ['name' => $closed_by, 'team_id' => $teamId],
                    [ 'password' => bcrypt('Password@123'), 'username' => $username, 'locations'=> $locsStruser ]
                );
                $closedByUserId = $user->id;
            }
        }

        if ($user) {
            if ($level1) { $user->categories()->syncWithoutDetaching([$level1->id]); }
            if ($level2) { $user->categories()->syncWithoutDetaching([$level2->id]); }
            if ($level3) { $user->categories()->syncWithoutDetaching([$level3->id]); }
            if ($counter) {
                $user->counter_id = $counter->id;
                $assigned = $user->assigned_counters ? json_decode($user->assigned_counters, true) : [];
                if (!is_array($assigned)) { $assigned = []; }
                $assigned[] = $counter->id;
                $assigned = array_values(array_unique($assigned));
                $user->assigned_counters = json_encode($assigned);
            }
            $user->save();
        }

        $queue = new Queue();
        $queue->team_id = $teamId;
        $queue->status = Queue::STATUS_PENDING;
        $queue->locations_id = $this->locationsId ?: null;
        if (!empty($token)) {
            $queue->token = preg_replace('/\D+/', '', $token) ?: null;
            $queue->start_acronym = preg_replace('/\d+/', '', $token) ?: null;
        }
        $queue->save();
        $this->created++;

        $qs = new QueueStorage();
        $qs->team_id = $teamId;
        $qs->queue_id = $queue->id;
        $qs->name = $name ?: null;
        $qs->phone = $phone ?: null;
        $jsonPayload = ['name' => $name, 'email' => $email];
        if (!empty($phone)) { $jsonPayload['phone'] = $phone; }
        if (!empty($descriptions)) { $jsonPayload['descriptions'] = $descriptions; }
        if (!empty($rating)) { $jsonPayload['rating'] = $rating; }
        $qs->json = json_encode($jsonPayload);
        // $qs->status = QueueStorage::STATUS_PENDING;
        $qs->locations_id = $this->locationsId ?: null;
        $qs->category_id = $level1?->id;
        $qs->sub_category_id = $level2?->id;
        $qs->child_category_id = $level3?->id;
        if (!empty($token)) {
            $qs->token = preg_replace('/\D+/', '', $token) ?: null;
            $qs->start_acronym = preg_replace('/\d+/', '', $token) ?: null;
        }
        if ($counter) { $qs->counter_id = $counter->id; }
        // Normalize arrives_time/datetime: parse or fallback to now() to satisfy NOT NULL schemas
        $dt = null;
        if (!empty($arrives_time)) {
            try { $dt = Carbon::parse($arrives_time); } catch (\Throwable $e) { $dt = null; }
        }
        if ($dt == null) { $dt = now(); }
        $qs->arrives_time = $dt;
        $qs->datetime  = $dt;
        if ($closedByUserId) { $qs->closed_by = $closedByUserId; $qs->served_by = $closedByUserId; }
        // Always compute datetimes from provided response/serving times
        $start = !empty($arrives_time) ? Carbon::parse($arrives_time) : $dt;
        $respSec = $this->parseTimeToSeconds($responsetime);
        $servSec = $this->parseTimeToSeconds($servingtime);
        if ($respSec > 0) {
            $calledAt = (clone $start)->addSeconds($respSec);
            $qs->called_datetime = $calledAt; $qs->start_datetime = $calledAt; $qs->called = 'yes';
        }
        if ($servSec > 0) {
            $qs->closed_datetime = (clone $start)->addSeconds($servSec);
        }
        // Set status to PENDING if serving time is empty/whitespace, otherwise CLOSE
        $qs->status = empty(trim($servingtime)) 
            ? QueueStorage::STATUS_PENDING 
            : QueueStorage::STATUS_CLOSE;
       
        if ($status === 'cancelled') { $qs->status = QueueStorage::STATUS_CANCELLED; $qs->cancelled_datetime = now(); $qs->called = 'no'; }
        $qs->save();
    }

    protected function firstOrCreateCategory(int $teamId, int $levelId, ?int $parentId, string $name): ?Category
    {
        if ($name === '') return null;
        $category = Category::firstOrCreate([
            'team_id'  => $teamId,
            'level_id' => $levelId,
            'parent_id'=> $parentId,
            'name'     => $name,
        ], [
            'category_locations' => $this->locationsId ? [(string)$this->locationsId] : [],
            'display_on' => 'Display on Transfer & Ticket Screen',
            'for_screen' => 'Display on Walk-In & Appointment Screen',
            'booking_category_show_for' => 'Backend & Online Appointment Screen',
        ]);
        if ($category && $this->locationsId) {
            $locs = $category->category_locations ?? [];
            if (!is_array($locs)) { $locs = []; }
            $locsStr = array_map('strval', $locs);
            if (!in_array((string)$this->locationsId, $locsStr, true)) {
                $locs[] = (string)$this->locationsId;
                $category->category_locations = $locs;
                $category->save();
            }
        }
        return $category;
    }
}
