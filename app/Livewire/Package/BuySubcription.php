<?php

namespace App\Livewire\Package;

use App\Models\ShrivraPackage;
use App\Models\ShrivraPanelFeature;
use App\Models\Domain;
use App\Models\QueuePanelUpgrade;
use App\Models\User;
use App\Models\Queue;
use App\Models\AccountSetting;
use App\Models\Subscription as ModelSubscription;
use App\Models\SubscriptionItem;
use Livewire\Component;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Subscription;
use Stripe\Charge;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On; 
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use Stripe\Invoice;
use Illuminate\Support\Facades\Session;

class BuySubcription extends Component
{
    public $packages = [];
    public $features = [];
    public $allInvoices = [];

    public string $activeTab = 'plans'; 
    public $selectedPackage;
    public $selectedPackageId;
    public $currentPlan;
    public $billingCycle = 'monthly';
    public $showPaymentForm = false;

    public $card_name;
    public $card_number;
    public $exp_month;
    public $exp_year;
    public $cvc;
    public $stripeToken;
    public $address_line1;
    public $postal_code;
    public $city;
    public $state;
    public $country;
    public $currentSubscription;
    public $latestInvoiceUrl;
    public $countStaff;
    public $currentPackage;
    public $countVisitor;

    // SMS Plan properties
    public $smsPlans = [];
    public $selectedSmsPlan;
    public $selectedSmsPlanId;
    public $currentSmsBalance = 0;
    
    // Invoice modal properties
    public $showInvoiceModal = false;
    public $invoiceData = [];

    public function mount()
    {
        $this->packages = ShrivraPackage::with('features')
            ->where('type', 'QUEUE')
            ->where('show_page', '!=', 'Life Time Offer')
            ->orderBy('id')
            ->get();

        $this->features = ShrivraPanelFeature::where('type', 'QUEUE')
            ->orderBy('id')
            ->get();
        $teamId = auth()->user()->team_id;
          
        $this->currentPlan =Domain::where('team_id', $teamId)->first();

        $this->currentSubscription = ModelSubscription::where('domain_id', $this->currentPlan->id)
            ->latest()
            ->first();

         // 🔽 Get invoice URL
         $this->allInvoices = QueuePanelUpgrade::with('subscription')->where('team_id', $teamId)->orderBy('id', 'desc')->get();
         if(count($this->allInvoices) > 0){
             
             $upgrade = $this->allInvoices[0];
             $this->latestInvoiceUrl = $upgrade?->getLatestInvoicePdf();
            }
            
        $this->currentPackage = QueuePanelUpgrade::where('team_id', $teamId)->where('status','active')->orderBy('id', 'desc')->first();
      
        $this->countStaff =User::where('team_id',$teamId)->count();
        $this->countVisitor =Queue::where('team_id',$teamId)->whereMonth('arrives_time',date('M'))->count();
     
        // Get current SMS balance from authenticated user
        $this->currentSmsBalance = auth()->user()->sms_credits_balance ?? 0;

        // Fetch SMS plans from database
        $this->smsPlans = \DB::table('sms_plans')
            ->select('id', 'name', 'description', 'credit_amount as credits', 'price', 'currency_code', 'is_popular')
            ->where('is_active', true)
            ->orderBy('credit_amount', 'asc')
            ->get()
            ->toArray();
    }

    public function setActiveTab(string $tab)
{
    $this->activeTab = $tab;
   if($this->activeTab == 'plans'){
       $this->dispatch('card-element-append');
   }
}

    public function downloadInvoice($invoiceId)
    {
        $upgrade = QueuePanelUpgrade::with(['package', 'subscription'])->findOrFail($invoiceId);

        if ($upgrade->type === 'sms_plan') {
            $data = [
                'invoice' => $upgrade,
                'user' => auth()->user(),
                'company' => [
                    'name' => 'SMS Solutions Inc.',
                    'address' => '123 Business Avenue',
                    'city' => 'San Francisco, CA',
                    'phone' => '+1 (555) 123-4567',
                ]
            ];
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sms_invoice', $data);
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'invoice-' . $upgrade->inv_num . '.pdf');
        }

        $url = $upgrade->getLatestInvoicePdf(); 
        $this->dispatch('triggerDownload', $url);
    }

public function changeType($type){
    $this->billingCycle = $type;
}

    public function selectPlan($id)
    {
        $this->selectedPackageId = $id;
        $this->selectedPackage = ShrivraPackage::with('features')->findOrFail($id);
        $this->showPaymentForm = false;

        $this->dispatch('scrollToCard');
    }


    #[On('stripeTokenReceived')]
    public function handleStripeToken($stripeToken, $cardName)
    {
        $this->card_name = $cardName;
        $this->stripeToken = $stripeToken;
        $this->pay();
    }

    #[On('smsStripeTokenReceived')]
    public function handleSmsStripeToken($stripeToken, $cardName)
    {
        $this->card_name = $cardName;
        $this->stripeToken = $stripeToken;
        $this->paySmsPlan();
    }

    #[On('paySmsPlan')]
    public function handleSmsPayment()
    {
        $this->paySmsPlan();
    }


//    public function pay()
// {
//     $teamId = auth()->user()->team_id;

//     $domain =  $this->currentPlan ?? Domain::where('team_id', $teamId)->first();

//     if (!$this->selectedPackage) {
//         session()->flash('error', 'Please select a plan before paying.');
//         // $this->dispatch('error');
//         $this->dispatch('error-plan', message:'Please select a plan before paying.');
//         return;
//     }

//     $price = $this->billingCycle === 'yearly'
//         ? $this->selectedPackage->price_yearly
//         : $this->selectedPackage->price;
// // dd($this->selectedPackage,$price,$this->billingCycle);
//     $currency = $this->selectedPackage->currency;

//     \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

//      // ✅ 1. Reuse Customer if already exists
//     $stripeCustomerId = $domain->stripe_id;
//     if ($stripeCustomerId) {
//         $customer = \Stripe\Customer::retrieve($stripeCustomerId);
//         if(empty($customer)){
//              $customer = \Stripe\Customer::create([
//             'email' => auth()->user()->email,
//             'source' => $this->stripeToken,
//             'name' => $this->card_name ?? auth()->user()->name ?? 'Guest',
//         ]);
//         }
//     } else {
//         $customer = \Stripe\Customer::create([
//             'email' => auth()->user()->email,
//             'source' => $this->stripeToken,
//             'name' => $this->card_name ?? auth()->user()->name ?? 'Guest',
//         ]);

//         // Save for future
//         $domain->update(['stripe_id' => $customer->id]);
//     }

//     $interval = $this->billingCycle === 'yearly' ? 'year' : 'month';

//     $queuePanelUpgrade = QueuePanelUpgrade::where('team_id',$teamId)->orderBy('id','desc')->first();

    
//     if(isset($queuePanelUpgrade->subcription_id) && $queuePanelUpgrade->subcription_id !=''){
//         try {
//             \Stripe\Subscription::retrieve($queuePanelUpgrade->subcription_id)->cancel();
//              $queuePanelUpgrade->update([
//             'status' => 'cancelled',
//         ]);

//          // ✅ Update local subscriptions table
//         $localSubscription = ModelSubscription::where('stripe_id', $queuePanelUpgrade->subcription_id)->first();
//         if ($localSubscription) {
//             $localSubscription->update([
//                 'stripe_status' => 'cancelled',
//                'ends_at' => now(),
//             ]);

//             // Optional: Delete or deactivate subscription_items
//             SubscriptionItem::where('subscription_id', $localSubscription->id)->delete();
//             // Or mark them inactive instead of delete
//             // ->update(['active' => false])
//         }

//         // Optional: also clear domain expiry
//         if ($domain) {
//             $domain->update(['expired' => now()]);
//         }

//         } catch (\Exception $e) {
//             // log or notify error if needed
//              $this->dispatch('error');
//         }
//     }


//     $existingPlans = \Stripe\Plan::all(['limit' => 100]); // Optional: add product filter
//    $plan = null;

// foreach ($existingPlans->data as $existingPlan) {
//     if (
//         $existingPlan->amount == ($price * 100) &&
//         $existingPlan->interval === $interval &&
//         strtolower($existingPlan->currency) === strtolower($currency)
//     ) {
//         $plan = $existingPlan;
//         break;
//     }
// }

// // STEP 2: If not found, create new plan
// if (!$plan) {
//     $product = \Stripe\Product::create([
//         'name' => $this->selectedPackage->name . ' ' . ucfirst($this->billingCycle),
//     ]);

//     $plan = \Stripe\Plan::create([
//         'amount' => $price * 100,
//         'interval' => $interval,
//         'currency' => strtolower($currency),
//         'product' => $product->id,
//     ]);
// }

//     $subscription = \Stripe\Subscription::create([
//         'customer' => $customer->id,
//         'items' => [['plan' => $plan->id]],
//     ]);

//     \Stripe\Charge::create([
//         'customer' => $customer->id,
//         'amount' => $price * 100,
//         'currency' => $currency,
//         'description' => 'Subscription to ' . $this->selectedPackage->name,
//     ]);

//      // ✅ Calculate expiry date
//     $expiryDate = $this->billingCycle === 'yearly'
//         ? now()->addYear()
//         : now()->addMonth();


//       // ✅ Save in domain table
//     $domain->update([
//         'stripe_id' => $customer->id,
//         'stripe_email' => $customer->email ?? Auth()->user()->email ?? '',
//         'expired' => $expiryDate,
//     ]);

//     $invoice = \Stripe\Invoice::retrieve($subscription->latest_invoice);
//    $invoiceNumber = $invoice->number;

//        QueuePanelUpgrade::create([
//         'team_id'        => $teamId,
//         'inv_num'        => $invoiceNumber,
//         'package_id'     => $this->selectedPackage->id,
//         'price'          => $price,
//         'unit'           => $this->billingCycle,
//         'type'           => 'QUEUE',
//         'date'           => now(),
//         'subcription_id' => $subscription->id,
//         'status' => $subscription->status,
//     ]);

    
// // Store subscription
// $localSubscription = ModelSubscription::create([
//     'domain_id'    =>$domain->id,
//     'type'   => 'QUEUE',                 // your own app-specific type
//     'stripe_id'       => $subscription->id,
//     'stripe_status'   => $subscription->status,
//     'stripe_price'    => $subscription->items->data[0]->price->id ?? null,
//     'quantity'        => $subscription->items->data[0]->quantity ?? 1,
//     'trial_ends_at' => isset($subscription->trial_end) ? Carbon::createFromTimestamp($subscription->trial_end) : null,
//     'ends_at'         => $subscription->current_period_end ? Carbon::createFromTimestamp($subscription->current_period_end) : null,
// ]);

// // Store items (loop in case of multiple items)
// foreach ($subscription->items->data as $item) {
//     SubscriptionItem::create([
//         'subscription_id'  => $localSubscription->id,
//         'stripe_id'        => $item->id,
//         'stripe_product'   => $item->price->product,
//         'stripe_price'     => $item->price->id,
//         'quantity'         => $item->quantity ?? 1,
//     ]);
// }
// //  $datetimeFormat = AccountSetting::showDateTimeFormat();

// if($subscription->status == 'active'){
//     $datetimeFormat = AccountSetting::showDateTimeFormat();
//     $invoiceUrl = $invoice->invoice_pdf;
//     $startDate = Carbon::createFromTimestamp($subscription->current_period_start);
// $data = [
//     // 'to_mail' => auth()->user()->email ?? '',
//     // 'to_mail' =>'aksh@stelleninfotech.in',
//     'to_mail' =>'aksh@stelleninfotech.in',
//     'team_id' =>$teamId,
//     'location_id' =>Session::get('selectedLocation') ?? '',
//     'plan' => $this->selectedPackage->name,
//     'price' => $price,
//     'billingCycle' => ucfirst($this->billingCycle),
//     'expiryDate' => $expiryDate->format($datetimeFormat),
//      'startDate'    => $startDate->format($datetimeFormat),
//     'invoice_url' => $invoiceUrl,
// ];
//     $this->sendNotification($data);
//     $this->dispatch('saved', message: 'Subscription successful!');
//     return;
// }
//  $this->dispatch('error');
//     // session()->flash('success', 'Subscription successful!');
//     // return redirect()->route('tenant.buy-subcription');
// }

public function pay()
{
    $teamId = auth()->user()->team_id;

    $domain = $this->currentPlan ?? Domain::where('team_id', $teamId)->first();

    if (!$this->selectedPackage) {
        $this->dispatch('show-error', message: 'Please select a plan before paying.');
        return;
    }

    // ✅ Handle Test Plan separately
    if ($this->selectedPackage['name'] === 'Test Plan') {
        $price = $this->selectedPackage['price']; // force 0 or ₹1
        $price = 50; // force 0 or ₹1
        $currency = $this->selectedPackage['currency'];
        $interval = 'day'; // 1-day interval
        $expiryDate = now()->addDay();
        $skipCharge = false; // Don't create Stripe Charge for Test Plan
    } else {
        $price = $this->billingCycle === 'yearly'
            ? $this->selectedPackage['price_yearly']
            : $this->selectedPackage['price'];

        $currency = $this->selectedPackage['currency'];
        $interval = $this->billingCycle === 'yearly' ? 'year' : 'month';
        $expiryDate = $this->billingCycle === 'yearly'
            ? now()->addYear()
            : now()->addMonth();
        $skipCharge = false;
    }

    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

    try {
        // ✅ Handle Stripe Customer
        $stripeCustomerId = $domain->stripe_id;
       $expired = $this->currentPlan->expired ?? date('Y-m-d');
        if(strtotime($expired) <= strtotime(date('Y-m-d')))
			{
				$expired = date('Y-m-d');
			}
			
			if($interval=='year')
			{
				$subscription_end = date('Y-m-d', strtotime("+1 year", strtotime($expired)));
			}
			elseif($interval=='month')
			{
				$subscription_end = date('Y-m-d', strtotime("+1 month", strtotime($expired)));
			}else{
                $subscription_end = date('Y-m-d', strtotime("+1 ", strtotime($expired)));
            }
		
        try {
            if ($stripeCustomerId) {
                $customer = \Stripe\Customer::retrieve($stripeCustomerId);
            } else {
                $customer = null;
            }
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $customer = null;
        }

        if (!$customer) {
            $customer = \Stripe\Customer::create([
                'email' => auth()->user()->email,
                'source' => $this->stripeToken ?? null,
                'name' => $this->card_name ?? auth()->user()->name ?? 'Guest',
            ]);

            $domain->update(['stripe_id' => $customer->id]);
        }

        // ✅ Cancel previous subscription if exists
        $queuePanelUpgrade = QueuePanelUpgrade::where('team_id', $teamId)->orderBy('id', 'desc')->first();
        if (isset($queuePanelUpgrade->subcription_id) && $queuePanelUpgrade->subcription_id != '') {
            try {

                $subscription_old = \Stripe\Subscription::retrieve($queuePanelUpgrade->subcription_id); 
         
                    if($subscription_old->status!="canceled")
                    {
                        $subscription_old->cancel();
                    }
                // $retrieve = \Stripe\Subscription::retrieve($queuePanelUpgrade->subcription_id);
                // $retrieve = \Stripe\Subscription::retrieve($queuePanelUpgrade->subcription_id)->cancel();
                $queuePanelUpgrade->update(['status' => 'cancelled']);

                $localSubscription = ModelSubscription::where('stripe_id', $queuePanelUpgrade->subcription_id)->first();
                if ($localSubscription) {
                    $localSubscription->update([
                        'stripe_status' => 'cancelled',
                        'ends_at' => now(),
                    ]);
                    SubscriptionItem::where('subscription_id', $localSubscription->id)->delete();
                }

                $domain->update(['expired' => now()]);
            } catch (\Exception $e) {
                $this->dispatch('show-error', message: 'Failed to cancel previous subscription: ' . $e->getMessage());
                return;
            }
        }

        // ✅ Check for existing Stripe plan or create new
        $existingPlans = \Stripe\Plan::all(['limit' => 100]);
        $plan = null;
        foreach ($existingPlans->data as $existingPlan) {
            if (
                $existingPlan->amount == ($price * 100) &&
                $existingPlan->interval === $interval &&
                strtolower($existingPlan->currency) === strtolower($currency)
            ) {
                $plan = $existingPlan;
                break;
            }
        }

        if (!$plan) {
            $product = \Stripe\Product::create(['name' => $this->selectedPackage['name'] . ' ' . ucfirst($interval)]);
            $plan = \Stripe\Plan::create([
                'amount' => $price * 100,
                'interval' => $interval,
                'currency' => strtolower($currency),
                'product' => $product->id,
            ]);
        }

        // ✅ Create subscription
        $subscription = \Stripe\Subscription::create([
            'customer' => $customer->id,
            'items' => [['plan' => $plan->id]],
            "trial_end" => strtotime($subscription_end),		
        ]);

        // ✅ Stripe charge only if not Test Plan and meets minimum
        if (!$skipCharge) {
            $chargeAmount = $price * 100;

            // Stripe minimum for INR = 50 INR (5000 paise)
            // if ($currency === 'INR' && $chargeAmount < 500) {
            //     $chargeAmount = 5000;
            // }

            \Stripe\Charge::create([
                'customer' => $customer->id,
                'amount' => $chargeAmount,
                'currency' => $currency,
                'description' => 'Subscription to ' . $this->selectedPackage['name'],
            ]);
        }

        // ✅ Save subscription info
        $domain->update([
            'stripe_id' => $customer->id,
            'stripe_email' => $customer->email ?? auth()->user()->email ?? '',
            'expired' => $expiryDate,
        ]);

        $invoice = \Stripe\Invoice::retrieve($subscription->latest_invoice);
        $invoiceNumber = $invoice->number;

        QueuePanelUpgrade::create([
            'team_id'        => $teamId,
            'inv_num'        => $invoiceNumber,
            'package_id'     => $this->selectedPackage['id'],
            'price'          => $price,
            'unit'           => $interval,
            'type'           => 'QUEUE',
            'date'           => now(),
            'subcription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);

        // Store local subscription
        $localSubscription = ModelSubscription::create([
            'domain_id'       => $domain->id,
            'type'            => 'QUEUE',
            'stripe_id'       => $subscription->id,
            'stripe_status'   => $subscription->status,
            'stripe_price'    => $subscription->items->data[0]->price->id ?? null,
            'quantity'        => $subscription->items->data[0]->quantity ?? 1,
            'trial_ends_at'   => isset($subscription->trial_end) ? Carbon::createFromTimestamp($subscription->trial_end) : null,
            'ends_at'         => $subscription->current_period_end ? Carbon::createFromTimestamp($subscription->current_period_end) : null,
        ]);

        foreach ($subscription->items->data as $item) {
            SubscriptionItem::create([
                'subscription_id'  => $localSubscription->id,
                'stripe_id'        => $item->id,
                'stripe_product'   => $item->price->product,
                'stripe_price'     => $item->price->id,
                'quantity'         => $item->quantity ?? 1,
            ]);
        }

        // ✅ Send notification & show success
        $datetimeFormat = AccountSetting::showDateTimeFormat();
        $invoiceUrl = $invoice->invoice_pdf;
        $startDate = Carbon::createFromTimestamp($subscription->current_period_start);

        $data = [
            'to_mail' => 'aksh@stelleninfotech.in',
            'team_id' => $teamId,
            'location_id' => Session::get('selectedLocation') ?? '',
            'plan' => $this->selectedPackage['name'],
            'price' => $price,
            'billingCycle' => ucfirst($interval),
            'expiryDate' => $expiryDate->format($datetimeFormat),
            'startDate' => $startDate->format($datetimeFormat),
            'invoice_url' => $invoiceUrl,
        ];
        $this->sendNotification($data);
        $this->dispatch('saved', message: 'Subscription successful!');

    } catch (\Exception $e) {
        // ✅ Catch all errors and show via Swal
        $this->dispatch('show-error', message: $e->getMessage());
    }
}


public function sendNotification($data)
{
    // ✅ Prepare subject and view content
    $title = 'Your Subscription Confirmation - ' . $this->selectedPackage->name;
    $templateContent = view('emails.subscription_success', compact('data'))->render();

    // ✅ Basic email validation
    if (!empty($data['to_mail']) && filter_var($data['to_mail'], FILTER_VALIDATE_EMAIL)) {
        try {
            Mail::html($templateContent, function ($message) use ($data, $title) {
                // Uses default .env mail credentials
                $message->to($data['to_mail'])->subject($title);
            });
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: ' . $e->getMessage());
        }
    }
}

public function cancelSubscription()
{
    $teamId = auth()->user()->team_id;
    $datetimeFormat = AccountSetting::showDateTimeFormat();
    $latestUpgrade = QueuePanelUpgrade::where('team_id', $teamId)->latest()->first();

    if (!$latestUpgrade || empty($latestUpgrade->subcription_id)) {
        session()->flash('error', 'No active subscription found.');
        return;
    }

    try {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Cancel the subscription at Stripe
        $subscription = Subscription::retrieve($latestUpgrade->subcription_id);

       $subscription->cancel();

        // Update local DB if needed
        $latestUpgrade->update([
            'status' => 'cancelled',
        ]);

         // ✅ Update local subscriptions table
        $localSubscription = ModelSubscription::where('stripe_id', $subscription->id)->first();
        if ($localSubscription) {
            $localSubscription->update([
              'stripe_status' => 'cancelled',
               'ends_at' => now(),
            ]);

            // Optional: Delete or deactivate subscription_items
            SubscriptionItem::where('subscription_id', $localSubscription->id)->delete();
            // Or mark them inactive instead of delete
            // ->update(['active' => false])
        }

        // Optional: also clear domain expiry
        $domain = Domain::where('team_id', $teamId)->first();
        if ($domain) {
            $domain->update(['expired' => now()]);
        }

        $data = [
            // 'to_mail' => auth()->user()->email ?? '',
            'to_mail' =>'aksh@stelleninfotech.in',
            'plan' => $latestUpgrade->package->name ?? 'Unknown Plan',
            'cancelled_at' => now()->format($datetimeFormat),
            'team_id' =>$teamId,
            'location_id' =>Session::get('selectedLocation') ?? '',
        ];

        $this->sendCancellationEmail($data);
        $this->dispatch('cancel', message: 'Subscription cancelled successfully.');
    } catch (\Exception $e) {
        logger()->error('Stripe cancel error: ' . $e->getMessage());
        session()->flash('error', 'Failed to cancel subscription. Try again later.');
    }
}

public function sendCancellationEmail($data)
{
    $title = 'Subscription Cancelled - ' . ($data['plan'] ?? '');

    $templateContent = view('emails.subscription_cancelled', compact('data'))->render();

    if (!empty($data['to_mail']) && filter_var($data['to_mail'], FILTER_VALIDATE_EMAIL)) {
        try {
            Mail::html($templateContent, function ($message) use ($data, $title) {
                $message->to($data['to_mail'])->subject($title);
            });
        } catch (\Exception $e) {
            Log::error('Mail sending failed (cancel): ' . $e->getMessage());
        }
    }
}

public function selectSmsPlan($planId)
{
    $this->selectedSmsPlanId = $planId;
    $this->selectedSmsPlan = collect($this->smsPlans)->firstWhere('id', $planId);
    $this->dispatch('scrollToCard');
}

public function paySmsPlan()
{
    $teamId = auth()->user()->team_id;
    $domain = $this->currentPlan ?? Domain::where('team_id', $teamId)->first();

    if (!$this->selectedSmsPlan) {
        $this->dispatch('show-error', message: 'Please select an SMS plan before paying.');
        return;
    }

    $price = $this->selectedSmsPlan->price;
    // $currency = $this->selectedSmsPlan->currency_code;
    $currency = $this->selectedSmsPlan->currency_code !== 'USD' ? 'USD' : 'USD';


    \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

    try {
        // Handle Stripe Customer
        $stripeCustomerId = $domain->stripe_id;
        
        try {
            if ($stripeCustomerId) {
                $customer = \Stripe\Customer::retrieve($stripeCustomerId);
            } else {
                $customer = null;
            }
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $customer = null;
        }

        if (!$customer) {
            $customer = \Stripe\Customer::create([
                'email' => auth()->user()->email,
                'source' => $this->stripeToken ?? null,
                'name' => $this->card_name ?? auth()->user()->name ?? 'Guest',
            ]);

            $domain->update(['stripe_id' => $customer->id]);
        }

        // Create one-time charge for SMS credits
        $charge = \Stripe\Charge::create([
            'customer' => $customer->id,
            'amount' => $price * 100,
            'currency' => strtolower($currency),
            'description' => 'SMS Credits Purchase - ' . $this->selectedSmsPlan->credits . ' credits',
        ]);

        // \Log::info('Stripe Charge Full JSON', json_decode($charge->toJSON(), true));

        // Create a subscription record for tracking (even though it's one-time)
        $localSubscription = ModelSubscription::create([
            'domain_id' => $domain->id,
            'type' => 'sms_plan',
            'stripe_id' => $charge->id,
            'stripe_status' => 'active',
            'stripe_price' => null,
            // 'quantity' => null,
            'quantity' => $this->selectedSmsPlan->credits,
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        // Create subscription item
        SubscriptionItem::create([
            'subscription_id' => $localSubscription->id,
            'stripe_id' => $charge->id,
            'stripe_product' => 'SMS Credits',
            'stripe_price' => $price,
            'quantity' => $this->selectedSmsPlan->credits,
        ]);

        // Create invoice record
        QueuePanelUpgrade::create([
            'team_id' => $teamId,
            'inv_num' => 'INV-SMS-' . time(),
            'package_id' => null,
            'price' => $price,
            'unit' => 'one-time',
            'type' => 'sms_plan',
            'date' => now(),
            'subcription_id' => $charge->id,
            'status' => 'completed',
        ]);

        // Update user's sms_credits_balance
        $user = auth()->user();
        // Comment: Previously added credits to balance
        // $user->sms_credits_balance = ($user->sms_credits_balance ?? 0) + $this->selectedSmsPlan->credits;
        // Now adding price instead of credits
        $user->sms_credits_balance = ($user->sms_credits_balance ?? 0) + $this->selectedSmsPlan->price;
        $user->save();

       // Send Payment Notifications

    //    Log::info('Charge Status: ' . $charge->status);
       if($charge->status == 'succeeded'){
            try {
                $paymentDetails = [
                    'amount' => $price,
                    'payment_id' => $charge->id,
                    'payment_date' => now()->format('d M, Y h:i A'),
                    'wallet_balance' => $user->sms_credits_balance,
                ];

                app(\App\Services\SMS\PaymentNotificationService::class)->sendPaymentFullSuccessNotification($user, $paymentDetails);
            } catch (\Exception $ex) {
                \Log::error('Payment Notification Error: ' . $ex->getMessage());
            }
       }

        $this->dispatch('saved', message: 'SMS Credits purchased successfully!');

    } catch (\Exception $e) {
        $this->dispatch('show-error', message: $e->getMessage());
    }
}

    public function openInvoiceModal($invoiceId)
    {
        $invoice = QueuePanelUpgrade::with(['package', 'subscription'])->findOrFail($invoiceId);
        
        $this->invoiceData = [
            'id' => $invoice->id,
            'invoice_number' => $invoice->inv_num,
            'date' => $invoice->date,
            'package_name' => $invoice->package ? $invoice->package->name : 'SMS Credits Package',
            'quantity' => $invoice->type === 'sms_plan' 
                ? ($invoice->subscription->quantity ?? intval($invoice->price / 0.089)) 
                : 1,
            'price' => $invoice->price,
            'currency' => $invoice->package ? $invoice->package->currency : 'USD',
            'total' => $invoice->price,
            'type' => $invoice->type,
        ];
        
        $this->showInvoiceModal = true;
    }

public function closeInvoiceModal()
{
    $this->showInvoiceModal = false;
    $this->invoiceData = [];
}


    public function render()
    {
        return view('livewire.package.buy-subcription');
    }
}
