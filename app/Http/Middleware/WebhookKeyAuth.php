<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CompanySubscriptionPlan;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class WebhookKeyAuth
{
    /**
     * Handle an incoming request.
     * Verify webhook key from header or request
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get webhook key from header or request
        $webhookKey = $request->header('X-Webhook-Key') 
                   ?? $request->header('Webhook-Key')
                   ?? $request->input('webhook_key')
                   ?? $request->input('app_key');

        if (empty($webhookKey)) {
            return response()->json([
                'status' => false,
                'message' => 'Webhook key is required. Provide it via X-Webhook-Key header or webhook_key/app_key parameter.',
            ], 401);
        }

        // Validate webhook key exists in database
        $validator = Validator::make(['webhook_key' => $webhookKey], [
            'webhook_key' => [
                'required',
                Rule::exists((new Company())->getTable(), 'app_key')
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid webhook key.',
                'messages' => $validator->errors(),
            ], 401);
        }

        // Verify company is active
        $company = Company::where('app_key', $webhookKey)
            ->where('status', 'active')
            ->first();

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company associated with this webhook key is inactive.',
            ], 401);
        }

        // Get current latest subscription plan using CompanySubscriptionPlan model
        $currentLatestPlan = CompanySubscriptionPlan::where('company_id', $company->id)
            ->where('plan_id', $company->plan_id)
            ->orderBy('id', 'desc')
            ->first();

        if (!$currentLatestPlan) {
            return response()->json([
                'status' => false,
                'message' => 'Company subscription plan not found.',
            ], 401);
        }

        // Check if plan expiry date is valid and not expired
        if ($currentLatestPlan->plan_expiry_date) {
            $planExpiryDate = Carbon::parse($currentLatestPlan->plan_expiry_date);
            $now = Carbon::now();

            if ($now->greaterThanOrEqualTo($planExpiryDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company subscription plan has expired.',
                ], 401);
            }
        }

        // Attach company and plan to request for use in controllers
        $request->merge(['verified_company' => $company]);
        $request->merge(['verified_plan' => $currentLatestPlan]);
        $request->merge(['webhook_key' => $webhookKey]);

        return $next($request);
    }
}

