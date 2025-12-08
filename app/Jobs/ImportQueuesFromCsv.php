<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Counter;
use App\Models\Queue;
use App\Models\QueueStorage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

class ImportQueuesFromCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $path;
    public int $locationsId;
    public ?int $teamId;
    protected int $createdCount = 0;

    /**
     * @param string $path storage path to the CSV file
     * @param int $locationsId optional location id
     * @param int|null $teamId pass current tenant/team id for safety
     */
    public function __construct(string $path, int $locationsId = 0, ?int $teamId = null)
    {
        $this->path = $path;
        $this->locationsId = $locationsId;
        $this->teamId = 3;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $fullPath = public_path($this->path);
        if (!file_exists($fullPath)) {
            Log::warning('Import CSV not found', ['path' => $fullPath]);
            return;
        }

        $stream = fopen($fullPath, 'r');
        if ($stream === false) {
            Log::error('Failed to open CSV stream', ['path' => $fullPath]);
            return;
        }

        // Detect delimiter using the first non-empty line
        $firstLine = '';
        while (($firstLine = fgets($stream)) !== false) {
            if (trim($firstLine) !== '') break;
        }
        $delimiter = ',';
        if ($firstLine !== false) {
            $counts = [
                ','  => substr_count($firstLine, ','),
                ';'  => substr_count($firstLine, ';'),
                "\t" => substr_count($firstLine, "\t"),
            ];
            arsort($counts);
            $top = array_key_first($counts);
            if ($counts[$top] > 0) {
                $delimiter = $top;
            }
        }
        rewind($stream);

        $header = null;
        $rowNumber = 0;
        while (($data = fgetcsv($stream, 0, $delimiter)) !== false) {
            $rowNumber++;
            // Skip empty lines
            if ($data === null || (count($data) === 1 && trim($data[0]) === '')) {
                continue;
            }

            if ($header === null) {
                $candidate = array_map(function ($h) {
                    return trim($h);
                }, $data);
                $lower = array_map('strtolower', $candidate);
                $isHeader = in_array('token', $lower)
                    || in_array('destination1', $lower)
                    || in_array('destination', $lower)
                    || in_array('airlines', $lower)
                    || in_array('flight number', $lower)
                    || in_array('flightnumber', $lower)
                    || in_array('counter', $lower)
                    || in_array('name', $lower)
                    || in_array('customer', $lower);
                if ($isHeader) {
                    $header = $candidate;
                }
                // keep scanning until header found
                continue;
            }

            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = $data[$i] ?? '';
            }

            try {
                $this->importRow($row);
            } catch (Throwable $e) {
                Log::error('Import row failed', ['rowNumber' => $rowNumber, 'row' => $row, 'error' => $e->getMessage()]);
            }
        }
        fclose($stream);
        Log::info('Import finished', [
            'path' => $this->path,
            'created_queues' => $this->createdCount,
            'locations_id' => $this->locationsId,
            'team_id' => $this->teamId,
        ]);
    }

    protected function importRow(array $row): void
    {
 
        $teamId = 3;

        // CSV column mapping
        $token        = trim($row['Token'] ?? $row['token'] ?? '') ?: null;
        $destination1 = trim($row['Destination1'] ?? $row['Destination'] ?? $row['Service'] ?? ''); // Level 1
        $airlines     = trim($row['Airlines'] ?? ''); // Level 2
        $flightNumber = trim($row['Flight Number'] ?? $row['FlightNumber'] ?? ''); // Level 3
        $counterName  = trim($row['Counter'] ?? '');
        $name         = trim($row['Name'] ?? $row['Customer'] ?? '');
        $phone         = trim($row['Contact'] ?? '');
        $email         = trim($row['Email'] ?? '');
        $arrives_time  = trim($row['Date & Time'] ?? ''); // in csv October 10 2025 10:35 AM
        $closed_by     = trim($row['Closed By']
            ?? $row['ClosedBy']
            ?? $row['closed by']
            ?? $row['closed_by']
            ?? $row['closedby']
            ?? ''); // user name
        $responsetime  = trim($row['Response Time (Minutes)'] ?? ''); // minutes
        $servingtime   = trim($row['Serving Time (Minutes)'] ?? ''); // minutes
        $statusRaw     = trim($row['Status'] ?? $row['status'] ?? $row['Closed Status'] ?? $row['closed status'] ?? '');
        $status        = strtolower($statusRaw);

      

        if ($destination1 === '' && $airlines === '' && $flightNumber === '') {
            // Nothing to import
            return;
        }

        // Upsert categories hierarchy
        $level1 = $this->firstOrCreateCategory($teamId, Category::STEP_1, null, $destination1);
        $level2 = $airlines !== '' ? $this->firstOrCreateCategory($teamId, Category::STEP_2, $level1?->id, $airlines) : null;
        $level3 = $flightNumber !== '' ? $this->firstOrCreateCategory($teamId, Category::STEP_3, ($level2?->id ?? $level1?->id), $flightNumber) : null;

        // Counter (optional)
        $counter = null;
        if ($counterName !== '') {
            $counter = Counter::firstOrCreate([
                'team_id' => $teamId,
                'name'    => $counterName,
            ], [
                'show_checkbox' => true,
                'counter_locations' => $this->locationsId ? [(string)$this->locationsId] : [],
            ]);
            // Ensure existing counter has current location in JSON
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

        $closedByUserId = null;
        $user = null;
        $locsStruser=[];
        if ($closed_by !== '') {
            // Only allow letters and spaces; skip user creation if name has any special characters or signs
            if (!preg_match('/[^A-Za-z\s]/', $closed_by)) {
                
                if (!in_array((string)$this->locationsId, $locsStruser, true)) {
                    $locsStruser[] = (string)$this->locationsId;
                }
                // Build a unique username from the name
                $baseUsername = Str::slug($closed_by, '_');
                $username = $baseUsername !== '' ? $baseUsername : 'user';
                $j = 1;
                while (User::where('username', $username)->exists()) {
                    $j++;
                    $username = $baseUsername . '_' . $j;
                }

                $user = User::firstOrCreate(
                    ['name' => $closed_by,'team_id'  => $teamId],
                    [
                        'password' => bcrypt('Password@123'),
                        'username' => $username,
                        'locations'=> $locsStruser,
                    ]
                );
                $closedByUserId = $user->id;
            } else {
                Log::info('Skipped user creation due to invalid characters in name', ['name' => $closed_by]);
            }
        }

        // Create relationship with new category, counter and user
        if ($user) {
            if ($level1) {
                $user->categories()->syncWithoutDetaching([$level1->id]);
            }
            if ($level2) {
                $user->categories()->syncWithoutDetaching([$level2->id]);
            }
            if ($level3) {
                $user->categories()->syncWithoutDetaching([$level3->id]);
            }
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


       

        // Create queue (parent)
        $queue = new Queue();
        $queue->team_id = $teamId;
        $queue->status = Queue::STATUS_PENDING;
        $queue->locations_id = $this->locationsId ?: null;
        if (!empty($token)) {
            $queue->token = preg_replace('/\D+/', '', $token) ?: null; // numeric part if any
            $queue->start_acronym = preg_replace('/\d+/', '', $token) ?: null; // alpha part if any
        }
        $queue->save();
        $this->createdCount++;



        // Create queue storage (child)
        $qs = new QueueStorage();
        $qs->team_id = $teamId;
        $qs->queue_id = $queue->id;
        $qs->name = $name ?: null;
        $qs->phone = $phone ?: null;
        $qs->json = json_encode([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
        ]);
        $qs->status = QueueStorage::STATUS_PENDING;
        $qs->locations_id = $this->locationsId ?: null;
        $qs->category_id = $level1?->id;
        $qs->sub_category_id = $level2?->id;
        $qs->child_category_id = $level3?->id;
        if (!empty($token)) {
            $qs->token = preg_replace('/\D+/', '', $token) ?: null; // numeric part if any
            $qs->start_acronym = preg_replace('/\d+/', '', $token) ?: null; // alpha part if any
        }
        if ($counter) {
            $qs->counter_id = $counter->id;
        }
        // minimal required timing fields
        $qs->arrives_time = $arrives_time; //2025-10-24 12:02:17
        $qs->datetime  = $arrives_time; //2025-10-24 12:02:17
        // Always set closed_by/served_by if we resolved a user, regardless of status

        if ($status === 'served' && $arrives_time) {
            $qs->status = QueueStorage::STATUS_CLOSE;
            $start_datetime = Carbon::parse($arrives_time);
            $called_minutes = (int)$responsetime;
            $serving_minutes = (int)$servingtime;
            $called_datetime = (clone $start_datetime)->addMinutes($called_minutes);

            $qs->called_datetime = $called_datetime;
            $qs->start_datetime = $called_datetime;

            $closed_datetime = (clone $start_datetime)->addMinutes($serving_minutes);
            $qs->closed_datetime = $closed_datetime;
            $qs->called = 'yes';
            if ($closedByUserId) {
            $qs->closed_by = $closedByUserId; // store user id
            $qs->served_by = $closedByUserId; // store user id
        }

        }
       
       
        if ($status === 'cancelled') {
            $qs->status = QueueStorage::STATUS_CANCEL;
            $qs->cancelled_datetime = now();
            $qs->called = 'no';  
            if ($closedByUserId) {
            $qs->closed_by = $closedByUserId; // store user id
            $qs->served_by = $closedByUserId; // store user id
        }

        }

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

        // Ensure existing category has current location in JSON
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
