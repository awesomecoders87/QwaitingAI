<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerActivityLog;
use App\Models\SiteDetail;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Imports\CustomerImport;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;


class CustomerList extends Component
{
    
    use WithPagination,WithFileUploads;
    

#[Title('Customer List')]

    public $teamId;
    public $location;
    public string $search = '';
    public $startDate;
    public $endDate;
    public $file;
    public $showimportDiv = false;
    public array $selectedCustomerIds = [];
    public bool $selectAllCustomers = false;

    //Add customer and Edit model
    public $customerId = null;
    public $name, $phone, $email, $image;
    public $showCustomerModal = false;
    public $editing = false;
        

    public function mount(){
         $this->teamId = tenant('id');
         $this->location = Session::get( 'selectedLocation' );
           
        // $today = now()->format('Y-m-d');
        // $this->startDate = $today;
        // $this->endDate = $today;
    }

   
    public function showImportModel()
    {
      
        $this->showimportDiv =  !$this->showimportDiv;
    }
 

  #[On('bulkDeleteCustomer')]
    public function updatedSelectAll($ids)
    {
        if ($ids) {
            $this->selectedCustomerIds = $ids;
        } else {
            $this->selectedCustomerIds = [];
        }

        $this->dispatch('confirm-multiple-delete');
    }

    #[On('confirmed-multiple-delete')]
    public function bulkDeleteStaff()
    {

        if (!empty($this->selectedCustomerIds)) {
            CustomerActivityLog::whereIn('customer_id', $this->selectedCustomerIds)->delete();
            Customer::whereIn('id', $this->selectedCustomerIds)->delete();
            $this->selectedCustomerIds = [];
            $this->selectAllCustomers = false;
            // session()->flash('message', 'Selected staff members deleted successfully.');
            $this->dispatch('deleted');
        }
    }



    public function updatingSearch()
    {
        $this->resetPage();
    }

     protected $rules = [
        'file' => 'required|file|mimes:csv,xlsx,xls',
    ];

 public function importCustomers()
    {
        $this->validate();

         Excel::import(
        new CustomerImport($this->teamId, $this->location),
        $this->file->getRealPath()
    );

        $this->file = null;

        session()->flash('message', 'Customers imported successfully.');
    }

public function exportCSV()
{
    $this->location = Session::get('selectedLocation');

    $query = Customer::query()
        ->where('team_id', $this->teamId)
        ->where('location_id', $this->location);

    if ($this->startDate && $this->endDate) {
        $query->whereBetween('created_at', [
            date('Y-m-d 00:00:00', strtotime($this->startDate)),
            date('Y-m-d 23:59:59', strtotime($this->endDate))
        ]);
    }

    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('phone', 'like', '%' . $this->search . '%')
              ->orWhereJsonContains('json->email', $this->search);
        });
    }

    $customers = $query->orderBy('created_at', 'desc')->get();

        // Add queue and booking counts
    $customers->transform(function ($customer) {
        $customer->queueCount = $customer->activityLogs()->where('type', 'queue')->count();
        $customer->bookingCount = $customer->activityLogs()->where('type', 'booking')->count();
        return $customer;
    });


    return Excel::download(new CustomersExport($customers), 'customers.csv');
}


    public function exportToPDF()
{
    $this->location = Session::get('selectedLocation');

    $query = Customer::query()->where('team_id', $this->teamId)
                              ->where('location_id', $this->location);

    // Apply date range filter if provided
    if ($this->startDate && $this->endDate) {
        $query->whereBetween('created_at', [
            date('Y-m-d 00:00:00', strtotime($this->startDate)),
            date('Y-m-d 23:59:59', strtotime($this->endDate))
        ]);
    }

    // Search filter (name, phone, JSON email)
    if (!empty($this->search)) {
        $query->where(function ($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('phone', 'like', '%' . $this->search . '%')
              ->orWhereJsonContains('json->email', $this->search);
        });
    }

    $customers = $query->orderBy('created_at', 'desc')->get();

    // Add queue and booking counts
    $customers->transform(function ($customer) {
        $customer->queueCount = $customer->activityLogs()->where('type', 'queue')->count();
        $customer->bookingCount = $customer->activityLogs()->where('type', 'booking')->count();
        return $customer;
    });
    
    $logo =  SiteDetail::viewImage(SiteDetail::FIELD_BUSINESS_LOGO, $this->teamId,$this->location);
    // Prepare data for PDF view
    $data = [
        'customers' => $customers,
        'dateformat' => auth()->user()->date_format ?? 'd M Y',
        'search' =>$this->search ?? '',
        'fromDate' =>$this->startDate,
        'toDate' =>$this->endDate,
        'logo_src' => $logo,  
    ];

    // Load the Blade view with customers data for PDF generation
    $pdf = Pdf::loadView('pdf.customers-pdf', $data)
              ->setPaper('a4', 'landscape');

    // Stream the PDF for download
    return response()->streamDownload(
        fn() => print($pdf->output()),
        'Customer-List.pdf'
    );
}

public function createCustomer()
{
    $this->reset(['customerId', 'name', 'phone', 'email', 'image']);
    $this->editing = false;
    $this->showCustomerModal = true;
}

public function editCustomer($id)
{
    $customer = Customer::where('team_id', $this->teamId)
                        ->where('location_id', $this->location)
                        ->findOrFail($id);

    $this->customerId = $customer->id;
    $this->name = $customer->name;
    $this->phone = $customer->phone;
    $this->email = $customer->json_data['email'] ?? '';
    $this->editing = true;
    $this->showCustomerModal = true;
}

public function saveCustomer()
{
    $this->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20|unique:customers,phone,' . $this->customerId . ',id,team_id,' . $this->teamId . ',location_id,' . $this->location,
        'email' => 'nullable|email',
        'image' => 'nullable|image|max:2048',
    ]);

    $name = $this->name ?? '';
    $phone = $this->phone ?? '';
    $data = [
        'team_id' => $this->teamId,
        'location_id' => $this->location,
        'name' => $name,
        'phone' => $phone,
        'json_data' => json_encode(['name'=>$name,'phone'=>$phone,'email' => $this->email]),
    ];

    if ($this->image) {
        $data['image'] = $this->image->store('customer_images', 'public');
    }

    if ($this->customerId) {
        Customer::where('id', $this->customerId)->update($data);
    } else {
        Customer::create($data);
    }

    $this->reset(['name', 'phone', 'email', 'image', 'customerId', 'editing']);
    $this->showCustomerModal = false;
    $this->dispatch('customer-saved');
}

    public function render()
    {
        $customers = Customer::query()
           ->where('team_id',$this->teamId )
           ->where('location_id',$this->location )
            ->when($this->search, fn($q) =>
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                          ->orWhere('phone', 'like', "%{$this->search}%");
                })
            )

            ->when($this->startDate && $this->endDate, fn($q) =>
                $q->whereBetween('created_at', [
                    date('Y-m-d 00:00:00', strtotime($this->startDate)),
                    date('Y-m-d 23:59:59', strtotime($this->endDate))
                ])
            )
            ->latest()
            ->paginate(10);

            // Append counts for queue and booking
    foreach ($customers as $customer) {
        $customer->queue_count = $customer->activityLogs->where('type', 'queue')->count();
        $customer->booking_count = $customer->activityLogs->where('type', 'booking')->count();
    }

        return view('livewire.customer-list', compact('customers'));
    }
}
