<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Exception;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe product and price for SMS plan
     * 
     * @param string $name Plan name
     * @param int $creditAmount Number of SMS credits
     * @param float $price Price amount
     * @param string $currency Currency code (e.g., 'usd')
     * @return string Stripe price ID
     */
    public function createSmsPlan($name, $creditAmount, $price, $currency)
    {
        try {
            // Create a product
            $product = Product::create([
                'name' => $name,
                'description' => "{$creditAmount} SMS Credits",
                'metadata' => [
                    'type' => 'sms_plan',
                    'credit_amount' => $creditAmount,
                ],
            ]);

            // Create a price for the product (one-time payment)
            $priceObj = Price::create([
                'product' => $product->id,
                'unit_amount' => (int)($price * 100), // Convert to cents
                'currency' => strtolower($currency),
                'metadata' => [
                    'credit_amount' => $creditAmount,
                ],
            ]);

            return $priceObj->id; // Return the price ID (this is the stripe_plan_id)
        } catch (Exception $e) {
            \Log::error('Stripe SMS Plan Creation Failed: ' . $e->getMessage());
            throw new Exception('Failed to create Stripe plan: ' . $e->getMessage());
        }
    }

    /**
     * Update a Stripe price (Note: Stripe prices are immutable, so we create a new one)
     * 
     * @param string $oldPriceId Old Stripe price ID
     * @param string $name Plan name
     * @param int $creditAmount Number of SMS credits
     * @param float $price Price amount
     * @param string $currency Currency code
     * @return string New Stripe price ID
     */
    public function updateSmsPlan($oldPriceId, $name, $creditAmount, $price, $currency)
    {
        try {
            // Archive the old price
            if ($oldPriceId) {
                Price::update($oldPriceId, ['active' => false]);
            }

            // Create a new price (Stripe prices are immutable)
            return $this->createSmsPlan($name, $creditAmount, $price, $currency);
        } catch (Exception $e) {
            \Log::error('Stripe SMS Plan Update Failed: ' . $e->getMessage());
            throw new Exception('Failed to update Stripe plan: ' . $e->getMessage());
        }
    }
}
