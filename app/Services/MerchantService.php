<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['api_key']), // Storing API key as password
            'type' => User::TYPE_MERCHANT,
        ]);

        return Merchant::create([
            'user_id' => $user->id,
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }


    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['api_key']), // Update API key as password
        ]);

        $merchant = $user->merchant;
        if ($merchant) {
            $merchant->update([
                'domain' => $data['domain'],
                'display_name' => $data['name'],
            ]);
        }
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */

    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method

        $user = User::where('email', $email)->first();

        return $user ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        $unpaidOrders = $affiliate->orders()->where('payout_status', Order::STATUS_UNPAID)->get();

        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }

    }


    /**
     * Get order statistics for the merchant within a specified date range.
     *
     * @param Merchant $merchant
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    public function getOrderStatistics(Merchant $merchant, string $fromDate, string $toDate): array
    {
        // Get order count, commission_owed, and revenue
        $orderStats = DB::table('orders')
            ->where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN payout_status = "unpaid" THEN commission_owed ELSE 0 END) as commission_owed'),
                DB::raw('SUM(subtotal) as revenue')
            )
            ->first();

        // Convert stdClass to array
        $orderStatsArray = (array)$orderStats;

        return $orderStatsArray;
    }

}
