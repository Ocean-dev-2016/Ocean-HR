<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Helpers\Helper;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

class CompanySubscriptionPlanExpired extends Command
{

    protected $signature = 'app:company-subscription-plan-expired';
    protected $description = 'Company Subscription Plan Expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('CompanySubscriptionPlanExpired command run at ' . now());
        try {
            $companies = Company::orderBy('id', 'DESC')->get();

            foreach ($companies as $company) {
                // Marks expired when plan_expiry_date has passed (7-day trial or paid plan)
                Helper::isCompanyPlanExpired($company, true);
            }

            $this->info('Company subscription plan status check completed.');

        } catch (\Exception $e) {
            Log::error('CompanySubscriptionPlanExpired command failed: ' . $e->getMessage());
        }
    }
}
