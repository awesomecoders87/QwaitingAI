<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\ShrivraPackage;
use App\Models\ShrivraPackageFeature;
use App\Models\ShrivraPanelFeature;
use App\Models\Currency;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ShrivraPackage::query();
            if ($request->filled('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }
            if ($request->filled('price')) {
                $query->where('price', $request->price);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            $packages = $query->orderBy('sorting', 'asc')->paginate(10)->appends($request->all());
            return view('superadmin.package.index', compact('packages'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load packages: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $currencies = Currency::select('ID', 'name', 'currency_code')->get();
        $featureList = ShrivraPanelFeature::where('type', 'QUEUE')->get();
        
        return view('superadmin.package.create', compact('currencies', 'featureList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
        ]);

        $package = ShrivraPackage::create($validated);

        // Save selected features
        if ($request->has('selectedFeatures')) {
            foreach ($request->selectedFeatures as $featureId => $data) {
                if (!empty($data['enabled']) && !empty($data['value'])) {
                    ShrivraPackageFeature::create([
                        'package_id' => $package->id,
                        'feature_id' => $featureId,
                        'feature_value' => $data['value'],
                    ]);
                }
            }
        }

        return redirect()->route('superadmin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    public function edit($id)
    {
        $package = ShrivraPackage::with('features')->findOrFail($id);
        $currencies = Currency::select('ID', 'name', 'currency_code')->get();
        $featureList = ShrivraPanelFeature::where('type', 'QUEUE')->get();
        
        // Prepare selected features
        $selectedFeatures = [];
        foreach ($package->features as $feature) {
            $selectedFeatures[$feature->feature_id] = [
                'enabled' => true,
                'value' => $feature->feature_value ?? '',
            ];
        }

        return view('superadmin.package.edit', compact('package', 'currencies', 'featureList', 'selectedFeatures'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
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
        ]);

        $package = ShrivraPackage::findOrFail($id);
        $package->update($validated);

        // Delete existing features and re-create
        ShrivraPackageFeature::where('package_id', $package->id)->delete();

        if ($request->has('selectedFeatures')) {
            foreach ($request->selectedFeatures as $featureId => $data) {
                if (!empty($data['enabled'])) {
                    ShrivraPackageFeature::create([
                        'package_id' => $package->id,
                        'feature_id' => $featureId,
                        'feature_value' => $data['value'] ?? '',
                    ]);
                }
            }
        }

        return redirect()->route('superadmin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    public function destroy($id)
    {
        ShrivraPackage::destroy($id);
        
        return redirect()->route('superadmin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }
}