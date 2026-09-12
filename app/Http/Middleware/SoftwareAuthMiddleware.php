<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CompanySubscriptionPlan;
use App\Models\Company;

class SoftwareAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin_software')->check()) {
            Auth::shouldUse('admin_software');
            return $next($request);
        }

        if (Auth::guard('employees')->check()) {
            Auth::shouldUse('employees');
            $user = Auth::guard('employees')->user();
            if ($user && $user->company_id) {
                $company = Company::find($user->company_id);
                if ($company && $company->plan_id) {
                    $latestPlan = CompanySubscriptionPlan::where('company_id', $company->id)
                        ->where('plan_id', $company->plan_id)
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!empty($latestPlan?->subscription_status) && $latestPlan?->subscription_status == 'expired') {
                        Auth::guard('employees')->logout();
                        if ($request->hasSession()) {
                            $request->session()->invalidate();
                        }
                        return Redirect::route('software.login')->withErrors(['plan_expired' => 'Your Plan is Expired.']);
                    }
                }
            }
            return $next($request);
        }

        return Redirect::route('software.login');
    }
}
