<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ApplicationVersion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApplicationVersionController extends Controller
{
    public function __construct(Request $request) { }

    public function checkVersion(Request $request)
    {
        try {
            // Fetch the latest version record
            $latest = ApplicationVersion::orderByDesc('id')->first();

            // If no version found, return fallback response
            if (!$latest) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No version found',
                    'data'    => null,
                ], 404);
            }

            // Build response data
            $responseData = [
                'latest_version'  => $latest->version,
                'apk_url'         => $latest->apk_file ? asset($latest->apk_file) : null,
                'update_message'  => $latest->update_message ?? ' ',
                'is_force_update' => (string) ($latest->is_force_update ?? false),
                'released_at'     => $latest->created_at?->toDateTimeString(),
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Latest version details fetched successfully',
                'data'    => $responseData,
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while fetching version details',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
