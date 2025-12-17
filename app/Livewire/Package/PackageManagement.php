<?php

namespace App\Livewire\Package;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ShrivraPackage;
use App\Models\Currency;
use App\Models\ShrivraPanelFeature;
use App\Models\ShrivraPackageFeature;
use App\Models\SmsPlan;
use App\Services\StripeService;

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

    public $activeTab = 'queue';

    // SMS Plan Properties
    public $smsName, $smsCreditAmount, $smsPrice, $smsCurrencyCode, $smsDescription;
    public $smsIsPopular = false;
    public $smsIsActive = true;
    public $smsPlanId;
    public $isSmsEditMode = false;

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

    protected $smsRules = [
        'smsName' => 'required|string|max:255',
        'smsCreditAmount' => 'required|integer|min:1',
        'smsPrice' => 'required|numeric|min:0',
        'smsCurrencyCode' => 'required|string|max:10',
        'smsDescription' => 'nullable|string|max:255',
        'smsIsPopular' => 'boolean',
        'smsIsActive' => 'boolean',
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

    public function switchTab($tab) 
    {
        $this->activeTab = $tab;
        $this->resetForm();
        $this->resetSmsForm();
    }

    public function resetSmsForm()
    {
        $this->reset([
            'smsName', 'smsCreditAmount', 'smsPrice', 'smsCurrencyCode', 
            'smsDescription', 'smsIsPopular', 'smsIsActive', 
            'smsPlanId', 'isSmsEditMode'
        ]);
    }

    public function storeSms()
    {
        $this->validate($this->smsRules);

        // If this plan is popular, remove popular flag from all other plans
        if ($this->smsIsPopular) {
            SmsPlan::where('is_popular', true)->update(['is_popular' => false]);
        }

        try {
            // Create Stripe plan
            $stripeService = new StripeService();
            $stripePlanId = $stripeService->createSmsPlan(
                $this->smsName,
                $this->smsCreditAmount,
                $this->smsPrice,
                $this->smsCurrencyCode
            );

            SmsPlan::create([
                'name' => $this->smsName,
                'credit_amount' => $this->smsCreditAmount,
                'price' => $this->smsPrice,
                'currency_code' => $this->smsCurrencyCode,
                'description' => $this->smsDescription,
                'stripe_plan_id' => $stripePlanId,
                'is_popular' => $this->smsIsPopular,
                'is_active' => $this->smsIsActive,
            ]);

            session()->flash('success', 'SMS Plan created successfully with Stripe integration.');
            $this->resetSmsForm();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create SMS Plan: ' . $e->getMessage());
        }
    }

    public function editSms($id)
    {
        $plan = SmsPlan::findOrFail($id);
        $this->smsPlanId = $id;
        $this->smsName = $plan->name;
        $this->smsCreditAmount = $plan->credit_amount;
        $this->smsPrice = $plan->price;
        $this->smsCurrencyCode = $plan->currency_code;
        $this->smsDescription = $plan->description;
        $this->smsIsPopular = (bool) $plan->is_popular;
        $this->smsIsActive = (bool) $plan->is_active;
        
        $this->isSmsEditMode = true;
    }

    public function updateSms()
    {
        $this->validate($this->smsRules);
        
        // If this plan is being set to popular, remove popular flag from all other plans
        if ($this->smsIsPopular) {
            SmsPlan::where('is_popular', true)
                ->where('id', '!=', $this->smsPlanId)
                ->update(['is_popular' => false]);
        }
        
        try {
            $plan = SmsPlan::findOrFail($this->smsPlanId);
            
            // Check if price or currency changed - need to update Stripe
            $needsStripeUpdate = ($plan->price != $this->smsPrice) || 
                                 ($plan->currency_code != $this->smsCurrencyCode) ||
                                 ($plan->credit_amount != $this->smsCreditAmount) ||
                                 ($plan->name != $this->smsName);
            
            $stripePlanId = $plan->stripe_plan_id;
            
            if ($needsStripeUpdate && $stripePlanId) {
                // Update Stripe plan (creates new price, archives old one)
                $stripeService = new StripeService();
                $stripePlanId = $stripeService->updateSmsPlan(
                    $plan->stripe_plan_id,
                    $this->smsName,
                    $this->smsCreditAmount,
                    $this->smsPrice,
                    $this->smsCurrencyCode
                );
            }
            
            $plan->update([
                'name' => $this->smsName,
                'credit_amount' => $this->smsCreditAmount,
                'price' => $this->smsPrice,
                'currency_code' => $this->smsCurrencyCode,
                'description' => $this->smsDescription,
                'stripe_plan_id' => $stripePlanId,
                'is_popular' => $this->smsIsPopular,
                'is_active' => $this->smsIsActive,
            ]);

            session()->flash('success', 'SMS Plan updated successfully.');
            $this->resetSmsForm();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update SMS Plan: ' . $e->getMessage());
        }
    }

    public function deleteSms($id)
    {
        SmsPlan::destroy($id);
        session()->flash('success', 'SMS Plan deleted successfully.');
    }

    protected function fillable()
    {
        return (new ShrivraPackage)->getFillable();
    }

    public function render()
    {
        return view('livewire.package.package-management',[
            'packages' => ShrivraPackage::orderBy('sorting', 'asc')->paginate(10),
            'smsPlans' => SmsPlan::orderBy('created_at', 'desc')->get(),
            'currencies' => Currency::query()->select('ID', 'name', 'currency_code')->get(),
        ]);
    }
}
