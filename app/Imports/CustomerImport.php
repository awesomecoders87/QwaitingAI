<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToCollection, WithHeadingRow
{
     protected $teamId;
    protected $locationId;

    public function __construct($teamId, $locationId)
    {
        $this->teamId = $teamId;
        $this->locationId = $locationId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Prepare JSON data by excluding standard columns (adjust as needed)
            $jsonData = [];

            foreach ($row->toArray() as $key => $value) {
                 $lowerKey = strtolower($key);
                // Assume 'name', 'phone', 'created_at' are standard columns
                // if (!in_array($key, ['name', 'phone'])) {
                    $jsonData[$key] = (string)$value;
                // }
            }

            // Find existing customer by name & phone or create new
            $customer = Customer::updateOrCreate(
                [
                     'team_id'     => $this->teamId,
                   'location_id' => $this->locationId,
                    'phone' => $row['phone'],
        ],
        [
                    'name' => $row['name'],
                    'json_data' => json_encode($jsonData),
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
