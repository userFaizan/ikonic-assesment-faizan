<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        // Check if the order with the given order_id already exists
        $existingOrder = Order::where('order_id', $data['order_id'])->first();

        if ($existingOrder) {
            // Order with this order_id already processed, ignore duplicate
            return;
        }

        // Check if there is an affiliate associated with the customer_email
        $user = $this->getUserByCustomerEmail($data['customer_email']);
        $affiliate = Affiliate::where('user_id', $user->id)->first();

        // If no affiliate found, create a new one
        if (!$affiliate) {
            $merchant = $this->getMerchantByDomain($data['merchant_domain']);
            $affiliate = $this->affiliateService->createAffiliate($user, $merchant);
        }

        // Create a new order
        $order = new Order([
            'order_id' => $data['order_id'],
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $this->getMerchantByDomain($data['merchant_domain'])->id,
            'affiliate_id' => $affiliate->id,
            'discount_code' => $data['discount_code'],
        ]);

        $order->save();

    }

    /**
     * Get User by Customer Email
     *
     * @param  string $customerEmail
     * @return User
     */
    protected function getUserByCustomerEmail(string $customerEmail): User
    {
        return User::firstOrCreate(['email' => $customerEmail], ['name' => $data['customer_name']]);
    }

    /**
     * Get Merchant by Domain
     *
     * @param  string $merchantDomain
     * @return Merchant
     */
    protected function getMerchantByDomain(string $merchantDomain): Merchant
    {
        return Merchant::where('domain', $merchantDomain)->firstOrFail();
    }
}
