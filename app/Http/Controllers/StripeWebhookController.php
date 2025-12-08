<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Domain;
use App\Models\QueuePanelUpgrade;
use App\Models\Subscription as ModelSubscription;
use Carbon\Carbon;
use Stripe\Subscription as StripeSubscription;
use Stripe\Invoice as StripeInvoice;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error("Stripe webhook signature error: " . $e->getMessage());
            return response('Invalid signature', 400);
        }

        if (!$event || !isset($event->type)) {
            Log::warning('Stripe webhook received invalid payload.', ['payload' => $payload]);
            return response('Invalid payload', 400);
        }

        Log::info("Stripe webhook received", ['type' => $event->type]);

        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event);
                break;

            default:
                Log::info("Unhandled Stripe webhook event type: " . $event->type);
        }

        return response('Webhook handled', 200);
    }

    private function handleInvoicePaymentSucceeded($event)
    {
        DB::beginTransaction();
        try {
            $subscriptionId   = $event->data->object->subscription;
            $stripeCustomerId = $event->data->object->customer;

            Log::info("Processing successful payment for subscription: $subscriptionId");

            $domain = Domain::where('stripe_id', $stripeCustomerId)->first();
            if (!$domain) {
                Log::warning("No domain found for Stripe customer ID: $stripeCustomerId");
                DB::rollBack();
                return;
            }

            $subscriptionObject = StripeSubscription::retrieve($subscriptionId);
            $endsAt   = Carbon::createFromTimestamp($subscriptionObject->current_period_end);
            $startAt  = Carbon::createFromTimestamp($subscriptionObject->current_period_start);
            $invoice  = StripeInvoice::retrieve($subscriptionObject->latest_invoice);
            $invoiceNumber = $invoice->number;

            Log::info("Stripe subscription object retrieved", [
                'start' => $startAt,
                'end'   => $endsAt,
                'invoice' => $invoiceNumber,
            ]);

            // Update domain expiry
            $domain->update(['expired' => $endsAt]);

            // 1️⃣ Mark previous subscription record as expired
            ModelSubscription::where('stripe_id', $subscriptionId)
                ->where('ends_at', '<', now())
                ->update(['stripe_status' => 'expired']);

            // 2️⃣ Create or update subscription record for new cycle
            $localSubscription = ModelSubscription::firstOrNew(['stripe_id' => $subscriptionId]);
            $localSubscription->fill([
                'domain_id'     => $domain->id,
                'type'          => 'QUEUE',
                'stripe_status' => $subscriptionObject->status,
                'stripe_price'  => $subscriptionObject->items->data[0]->price->id ?? null,
                'quantity'      => $subscriptionObject->items->data[0]->quantity ?? 1,
                'trial_ends_at' => $subscriptionObject->trial_end
                    ? Carbon::createFromTimestamp($subscriptionObject->trial_end)
                    : null,
                'ends_at'       => $endsAt,
                'start_date'    => $startAt,
                'cancel_at'     => $subscriptionObject->cancel_at
                    ? Carbon::createFromTimestamp($subscriptionObject->cancel_at)
                    : null,
                'canceled_at'   => $subscriptionObject->canceled_at
                    ? Carbon::createFromTimestamp($subscriptionObject->canceled_at)
                    : null,
            ]);
            $localSubscription->save();

            // 3️⃣ Update or insert QueuePanelUpgrade
            $queuePanelUpgrade = QueuePanelUpgrade::where('subcription_id', $subscriptionId)->latest()->first();
            if ($queuePanelUpgrade) {
                $queuePanelUpgrade->update([
                    'status'  => $subscriptionObject->status,
                    'inv_num' => $invoiceNumber,
                    'date'    => now(),
                ]);
            } else {
                $interval  = $subscriptionObject->items->data[0]->plan->interval;
                QueuePanelUpgrade::create([
                    'subcription_id' => $subscriptionId,
                    'status'         => $subscriptionObject->status,
                    'inv_num'        => $invoiceNumber,
                    'date'           => now(),
                    'team_id'        => $domain->team_id,
                    'price'          => $subscriptionObject->items->data[0]->price->unit_amount / 100 ?? 0,
                    'type'           => 'Webhook',
                    'interval'        => $interval,
                    'package_id'        => 5,
                   
                ]);
            }

            DB::commit();
            Log::info("Subscription renewal processed successfully", ['subscription_id' => $subscriptionId]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing Stripe webhook: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'payload' => $event,
            ]);
        }
    }

    private function handleSubscriptionDeleted($event)
    {
        $subscriptionId = $event->data->object->id;

        Log::info("Processing subscription cancellation for: {$subscriptionId}");

        ModelSubscription::where('stripe_id', $subscriptionId)->update([
            'stripe_status' => 'canceled',
            'canceled_at'   => now(),
        ]);

        QueuePanelUpgrade::where('subcription_id', $subscriptionId)->update([
            'status' => 'canceled',
            'date'   => now(),
        ]);
    }
}
