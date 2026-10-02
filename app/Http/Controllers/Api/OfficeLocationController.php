<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeLocationController extends Controller
{
    /**
     * Get Company Office Location (only latitude and longitude).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOfficeLocation(Request $request)
    {
        try {
            $employee = Auth::guard('employee-api')->user();
            $companyId = $request->input('company_id', $employee?->company_id);

            if (!$companyId) {
                // If not passed and not authenticated, fallback to the first active company
                $company = Company::first();
            } else {
                $company = Company::find($companyId);
            }

            if (!$company) {
                return response()->json([
                    'status' => false,
                    'message' => 'Company not found.',
                ], 404);
            }

            $data = [
                'latitude' => $company->latitude ? (float) $company->latitude : null,
                'longitude' => $company->longitude ? (float) $company->longitude : null,
                'radius' => $company->radius !== null ? (float) $company->radius : 100.0,
            ];

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
