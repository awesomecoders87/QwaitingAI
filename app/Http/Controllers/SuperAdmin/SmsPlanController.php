<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SmsPlanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = \App\Models\SmsPlan::query();
            if ($request->filled('search')) {
                $query->name($request->search);
            }
            if ($request->filled('status')) {
                $query->status($request->status);
            }
            if ($request->filled('popular')) {
                $query->popular($request->popular);
            }
            $smsPlans = $query->orderBy('created_at', 'desc')->get();
            return view('superadmin.smsPlan.index', compact('smsPlans'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load SMS plans: ' . $e->getMessage());
        }
    }

    public function create()
    {
        // $currencies = \App\Models\Currency::select('currency_code', 'currency_name')->get();
        $currencies = \App\Models\Currency::select('ID', 'name', 'currency_code')->get();

        return view('superadmin.smsPlan.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'credit_amount' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'currency_code' => 'required|string|max:10',
                'description' => 'nullable|string|max:255',
                'is_popular' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);
            $data['is_popular'] = $request->has('is_popular');
            $data['is_active'] = $request->has('is_active');
            
            // If this plan is being marked as popular, remove popular flag from all other plans
            if ($data['is_popular']) {
                \App\Models\SmsPlan::where('is_popular', true)->update(['is_popular' => false]);
            }
            
            \App\Models\SmsPlan::create($data);
            return redirect()->route('superadmin.sms-plans.index')->with('success', 'SMS Plan created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create SMS plan: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $plan = \App\Models\SmsPlan::findOrFail($id);
            // $currencies = \App\Models\Currency::select('currency_code', 'currency_name')->get();
            $currencies = \App\Models\Currency::select('ID', 'name', 'currency_code')->get();
            return view('superadmin.smsPlan.edit', compact('plan', 'currencies'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load SMS plan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $plan = \App\Models\SmsPlan::findOrFail($id);
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'credit_amount' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'currency_code' => 'required|string|max:10',
                'description' => 'nullable|string|max:255',
                'is_popular' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);
            $data['is_popular'] = $request->has('is_popular');
            $data['is_active'] = $request->has('is_active');
            
            // If this plan is being marked as popular, remove popular flag from all other plans
            if ($data['is_popular']) {
                \App\Models\SmsPlan::where('is_popular', true)
                    ->where('id', '!=', $id)
                    ->update(['is_popular' => false]);
            }
            
            $plan->update($data);
            return redirect()->route('superadmin.sms-plans.index')->with('success', 'SMS Plan updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update SMS plan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $plan = \App\Models\SmsPlan::findOrFail($id);
            $plan->delete();
            return redirect()->route('superadmin.sms-plans.index')->with('success', 'SMS Plan deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete SMS plan: ' . $e->getMessage());
        }
    }
}
