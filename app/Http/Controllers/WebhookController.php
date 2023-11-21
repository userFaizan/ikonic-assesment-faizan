<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $data = $request->all();

        // Validate the required data from the webhook payload
        $validatedData = $this->validateWebhookData($data);

        // Pass the validated data to the OrderService for processing
        $this->orderService->processOrder($validatedData);

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }
    /**
     * Validate the necessary data from the webhook payload
     *
     * @param  array $data
     * @return array
     */
    protected function validateWebhookData(array $data): array
    {
        // Validate based on the actual migration structures

        // Validate for the 'orders' table structure
        $orderValidationRules = [
            'order_id' => 'required|string',
            'subtotal_price' => 'required|numeric',
            'merchant_domain' => 'required|string',
            'discount_code' => 'required|string',
            'customer_email' => 'required|email',
            'customer_name' => 'required|string',
        ];

        // Validate for the 'merchants' table structure
        $merchantValidationRules = [
            'domain' => 'required|string',
        ];

        // Validate for the 'affiliates' table structure
        $affiliateValidationRules = [
            'commission_rate' => 'required|numeric',
            'discount_code' => 'required|string',
        ];

        // Validate for the 'users' table structure
        $userValidationRules = [
            'email' => 'required|email',
            'name' => 'required|string',
        ];

        // Run the validation rules for each table structure
        $validatedData = [
            'order' => $this->validateAgainstRules($data, $orderValidationRules),
            'merchant' => $this->validateAgainstRules($data, $merchantValidationRules),
            'affiliate' => $this->validateAgainstRules($data, $affiliateValidationRules),
            'user' => $this->validateAgainstRules($data, $userValidationRules),
        ];

        return $validatedData;
    }

    /**
     * Validate data against specific rules
     *
     * @param  array $data
     * @param  array $rules
     * @return array
     */
    protected function validateAgainstRules(array $data, array $rules): array
    {
        return $this->validate($data, $rules);
    }
}
