<?php

namespace App\Livewire\Package;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ShrivraPackage;
use App\Models\Currency;
use App\Models\ShrivraPanelFeature;
use App\Models\ShrivraPackageFeature;

class PackageManagement extends Component
{
    use WithPagination;

    public $name, $price, $price_yearly, $status = 'Active', $currency, $show_page = 'Pricing Page', $price_monthly_inr, $price_yearly_inr, $sorting;
    public $type ='QUEUE';
    public $packageId;
    public $currencyList = [];
    public $featureList = [];
    public $selectedFeatures = [];
    public $isEditMode = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'price_yearly' => 'nullable|numeric|min:0',
        'type' => 'nullable|string|max:255',
        'status' => 'nullable|in:Active,Inactive',
        'currency' => 'nullable|string|max:255',
        'show_page' => 'nullable|string|max:250',
        'price_monthly_inr' => 'nullable|numeric|min:0',
        'price_yearly_inr' => 'nullable|numeric|min:0',
        'sorting' => 'nullable|integer',
    ];

  
   public function mount(){
     $this->currencyList = Currency::query()->select('name', 'currency_code')->get();
     $this->featureList = ShrivraPanelFeature::where('type','QUEUE')->get();

      if ($this->isEditMode && $this->package) {
        $this->selectedFeatures = $this->package->features
            ->pluck('feature_value', 'feature_id')
            ->toArray();
    }
   }
  public function resetForm()
{
    $this->reset([
        'name', 'price', 'price_yearly', 'type', 'status', 'currency', 'show_page',
        'price_monthly_inr', 'price_yearly_inr', 'sorting', 'packageId', 'isEditMode',
        'selectedFeatures'
    ]);
}
   public function store()
{
    $this->validate();
// dd($this->fillable(),$this->selectedFeatures);
    $package = ShrivraPackage::create($this->only($this->fillable()));

    // Save selected features
  foreach ($this->selectedFeatures as $featureId => $data) {
        if (!empty($data['enabled']) && !empty($data['value'])) {
            ShrivraPackageFeature::create([
                'package_id' => $package->id,
                'feature_id' => $featureId,
                'feature_value' => $data['value'],
            ]);
        }
    }

    session()->flash('success', 'Package created successfully.');
    $this->resetForm();
}

public function edit($id)
{
    $package = ShrivraPackage::findOrFail($id);

    $this->fill($package->only($this->fillable()));
    $this->packageId = $id;
    $this->isEditMode = true;

    // Set feature checkboxes and values
    $this->selectedFeatures = [];
    foreach ($package->features as $feature) {
        $this->selectedFeatures[$feature->feature_id] = [
            'enabled' => true,
            'value' => $feature->feature_value ?? '',
        ];
    }
     $this->currencyList = Currency::query()->select('name', 'currency_code')->get();
   
}

 public function update()
{
    $this->validate();

    $package = ShrivraPackage::findOrFail($this->packageId);
    $package->update($this->only($this->fillable()));

    ShrivraPackageFeature::where('package_id', $package->id)->delete();

    foreach ($this->selectedFeatures as $featureId => $data) {
        if (!empty($data['enabled'])) {
            ShrivraPackageFeature::create([
                'package_id' => $package->id,
                'feature_id' => $featureId,
                'feature_value' => $data['value'] ?? '',
            ]);
        }
    }

    session()->flash('success', 'Package updated successfully.');
    $this->resetForm();
}
    public function delete($id)
    {
        ShrivraPackage::destroy($id);
        session()->flash('success', 'Package deleted successfully.');
    }

    protected function fillable()
    {
        return (new ShrivraPackage)->getFillable();
    }

    public function render()
    {
        return view('livewire.package.package-management',[
            'packages' => ShrivraPackage::orderBy('sorting', 'asc')->paginate(10)
        ]);
    }
}
