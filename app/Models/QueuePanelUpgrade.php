<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ShrivraPackage;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Invoice;

class QueuePanelUpgrade extends Model
{
    protected $table = "queue_panel_upgrade";

    protected $fillable = [
        'team_id',
        'inv_num',
        'package_id',
        'price',
        'unit',
        'type',
        'date',
        'subcription_id',
        'status'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(ShrivraPackage::class, 'package_id');
    }

            public function subscription()
            {
                return $this->hasOne(\App\Models\Subscription::class, 'stripe_id', 'subcription_id');
            }

    /**
     * Get the latest Stripe invoice PDF URL for this subscription
     */
    public function getLatestInvoicePdf()
    {
        if (!$this->subcription_id) {
            return null;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            if ($this->type === 'sms_plan') {
                $charge = \Stripe\Charge::retrieve($this->subcription_id);
                return $charge->receipt_url ?? null;
            }

            // Retrieve subscription and latest invoice
            $subscription = Subscription::retrieve($this->subcription_id);
            $invoice = Invoice::retrieve($subscription->latest_invoice);

            return $invoice->invoice_pdf ?? null;
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve invoice PDF: ' . $e->getMessage());
            return null;
        }
    }
}
