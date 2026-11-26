<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeLocationController extends Controller
{
    /**
     * Get Company Office Location (latitude, longitude, radius).
     *
     * Accepts optional company_id. If omitted, uses authenticated employee company.
     */
    public function getOfficeLocation(Request $request)
    {
        try {
            $employee = Auth::guard('employee-api')->user();
            $companyId = $request->input('company_id') ?: $employee?->company_id;

            if (!$companyId) {
                return response()->json([
                    'status' => false,
                    'message' => 'company_id is required (or login as employee).',
                ], 422);
            }

            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company not found.',
                ], 404);
            }

            // Only web-panel / DB values — no static lat/long/radius
            $latitude = ($company->latitude !== null && $company->latitude !== '')
                ? (float) $company->latitude
                : null;
            $longitude = ($company->longitude !== null && $company->longitude !== '')
                ? (float) $company->longitude
                : null;
            $radius = ($company->radius !== null && $company->radius !== '')
                ? (float) $company->radius
                : null;

            $data = [
                'company_id' => (string) $company->id,
                'company_name' => $company->company_name,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius' => $radius,
            ];

            if ($latitude === null || $longitude === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Office latitude/longitude not set in company (web panel).',
                    'data' => $data,
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Office location retrieved successfully.',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve office location: ' . $e->getMessage(),
            ], 500);
        }
    }
}
