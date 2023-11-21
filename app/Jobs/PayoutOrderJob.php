<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method
        try {
            // Assuming  want to send the payout to the customer's email associated with the order
            $customerEmail = $this->order->customer_email;

            // Calculate the payout amount based on your logic, for example, using the order subtotal
            $payoutAmount = $this->order->subtotal;

            // Call the API service to send a payout
            $apiService->sendPayout($customerEmail, $payoutAmount);

            // If the payout is successful, update the order status to paid
            $this->order->update(['payout_status' => Order::STATUS_PAID]);
        } catch (Exception $e) {
            // Log the exception or handle it as needed
            // If there's an exception, the order status remains unpaid
            // You may want to log the exception or take other actions based on your application logic
            \Log::error('Payout failed for order ' . $this->order->id . ': ' . $e->getMessage());
        }

    }
}
