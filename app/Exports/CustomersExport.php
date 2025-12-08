<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, WithHeadings
{
    protected $customers;
    protected $jsonKeys = [];

    public function __construct(Collection $customers)
    {
        $this->customers = $customers;

        $allKeys = [];

        foreach ($this->customers as $customer) {
            $jsonData = $customer->json_data;

            if (is_string($jsonData)) {
                $decoded = json_decode($jsonData, true);
                $decoded = is_array($decoded) ? $decoded : [];
            } elseif (is_array($jsonData)) {
                $decoded = $jsonData;
            } else {
                $decoded = [];
            }

            $filteredKeys = array_diff(array_keys($decoded), ['name', 'phone']);
            $allKeys = array_merge($allKeys, $filteredKeys);
        }

        $this->jsonKeys = array_unique($allKeys);
    }

    public function collection()
    {
        return $this->customers->map(function ($customer) {
            $jsonData = $customer->json_data;

            if (is_string($jsonData)) {
                $decoded = json_decode($jsonData, true);
                $decoded = is_array($decoded) ? $decoded : [];
            } elseif (is_array($jsonData)) {
                $decoded = $jsonData;
            } else {
                $decoded = [];
            }

            $baseData = [
                __('report.name') => $customer->name,
                __('report.Phone') => $customer->phone,
                __('report.Queue Count') => $customer->queueCount ?? 0,
                __('report.Booking Count') => $customer->bookingCount ?? 0,
                __('report.created at') => $customer->created_at->format('Y-m-d H:i:s'),
            ];

            foreach ($this->jsonKeys as $key) {
                $baseData[$key] = $decoded[$key] ?? '';
            }

            return $baseData;
        });
    }

    public function headings(): array
    {
       $staticHeadings = [
            __('report.name'),
           __('report.Phone'),
           __('report.Queue Count'),
            __('report.Booking Count'),
            __('report.created at'),
        ];

        $dynamicHeadings = array_map(function ($key) {
            $label = __('customers.' . $key);
            return $label !== 'customers.' . $key
                ? $label
                : ucfirst(str_replace('_', ' ', $key));
        }, $this->jsonKeys);

        return array_merge($staticHeadings, $dynamicHeadings);
    }
}
