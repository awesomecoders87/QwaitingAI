<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class VendorsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $domains;

    public function __construct(Collection $domains)
    {
        $this->domains = $domains;
    }
    
    private $serialNumber = 0;

    public function collection()
    {
        return $this->domains;
    }

    public function headings(): array
    {
        return [
            'Domain',
            'Tenant ID',
            'Tenant Name',
            'Owner Name',
            'Owner Email',
            'Owner Phone',
            'Owner Address',
            'Owner Username',
            'Created At',
            'Expires At',
            'Trial Ends At',
            'Status',
        ];
    }

    public function map($domain): array
    {
        $this->serialNumber++;
        
        $now = Carbon::now();
        $expiryDate = $domain->expired ? Carbon::parse($domain->expired) : null;
        $isExpired = $expiryDate && $expiryDate->isPast();
        $isExpiringSoon = $domain->isExpiringSoon();
        $isTrial = $domain->trial_ends_at && Carbon::parse($domain->trial_ends_at)->isFuture();
        
        // Determine status
        if ($isExpired) {
            $status = 'Expired';
        } elseif ($isExpiringSoon) {
            $status = 'Expiring Soon';
        } elseif ($isTrial) {
            $status = 'Trial';
        } else {
            $status = 'Active';
        }

        // Get admin user (owner)
        $adminUser = $domain->adminUser && $domain->adminUser->isNotEmpty() 
            ? $domain->adminUser->first() 
            : null;

        // Get tenant name
        $tenantName = 'N/A';
        if ($domain->team) {
            $teamData = $domain->team->data;
            if (is_array($teamData) && isset($teamData['name'])) {
                $tenantName = $teamData['name'];
            } elseif (is_string($teamData)) {
                $decoded = json_decode($teamData, true);
                $tenantName = is_array($decoded) && isset($decoded['name']) ? $decoded['name'] : 'N/A';
            }
        }

        return [
            $domain->domain,
            $domain->team_id ?? 'N/A',
            $tenantName,
            $adminUser ? $adminUser->name : 'N/A',
            $adminUser ? $adminUser->email : 'N/A',
            $adminUser ? $adminUser->phone : 'N/A',
            $adminUser ? $adminUser->address : 'N/A',
            $adminUser ? $adminUser->username : 'N/A',
            $domain->created_at ? $domain->created_at->format('Y-m-d H:i:s') : 'N/A',
            $domain->expired ? $expiryDate->format('Y-m-d') : 'No Expiry',
            $domain->trial_ends_at ? Carbon::parse($domain->trial_ends_at)->format('Y-m-d') : 'N/A',
            $status,
        ];
    }
}

