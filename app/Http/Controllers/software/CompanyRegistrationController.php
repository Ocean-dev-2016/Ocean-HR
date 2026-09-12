<?php

namespace App\Http\Controllers\software;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDetails;
use App\Models\CompanyRegistration;
use App\Models\CompanySubscriptionPlan;
use App\Models\MasterCountry;
use App\Models\PlanMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class CompanyRegistrationController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Company Registration',
            'folder_path' => 'software.modules.company_registration',
            'route' => 'company-registration',
            'table_name' => (new CompanyRegistration())->getTable(),
            'permisstion_prefix' => 'company-registration',
            'module_name' => 'Company Registration'
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;

        try {
            $columns = [
                (object)['data' => 'id', 'name' => 'id', 'td_label' => 'ID', 'className' => ''],
                (object)['data' => 'gst_no', 'name' => 'gst_no', 'td_label' => 'GST No', 'className' => ''],
                (object)['data' => 'company_name', 'name' => 'company_name', 'td_label' => 'Company Name', 'className' => ''],
                (object)['data' => 'person_name', 'name' => 'person_name', 'td_label' => 'Person Name', 'className' => ''],
                (object)['data' => 'whatsapp_number', 'name' => 'whatsapp_number', 'td_label' => 'WhatsApp Number', 'className' => ''],
                (object)['data' => 'email', 'name' => 'email', 'td_label' => 'Email', 'className' => ''],
                (object)['data' => 'plan_name', 'name' => 'plan.title', 'td_label' => 'Plan Name', 'className' => ''],
                (object)['data' => 'status', 'name' => 'status', 'td_label' => 'Status', 'className' => 'text-center'],
                (object)['data' => 'created_at', 'name' => 'created_at', 'td_label' => 'Registered Date', 'className' => ''],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];

            View::share('modules', $modules);
            View::share('columns', $columns);

            if ($request->ajax()) {
                $data = CompanyRegistration::with(['plan', 'country', 'state', 'city'])->latest();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('plan_name', function ($row) {
                        return $row->plan?->title ?? $row->plan?->name ?? '-';
                    })
                    ->editColumn('status', function ($row) {
                        if ($row->status === 'approved') {
                            return '<span class="badge bg-success">Approved</span>';
                        } elseif ($row->status === 'rejected') {
                            return '<span class="badge bg-danger">Rejected</span>';
                        }
                        return '<span class="badge bg-warning text-dark">Pending</span>';
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? $row->created_at->format('d-m-Y H:i A') : '-';
                    })
                    ->addColumn('action', function ($row) {
                        $btn = '';
                        if ($row->status === 'pending') {
                            $approveUrl = route('software.company-registration.approve', $row->id);
                            $rejectUrl = route('software.company-registration.reject', $row->id);

                            $btn .= '<button type="button" class="btn btn-sm btn-success me-1 btn-approve-request" data-url="' . $approveUrl . '" title="Approve"><i class="fa fa-check"></i> Approve</button>';
                            $btn .= '<button type="button" class="btn btn-sm btn-danger me-1 btn-reject-request" data-url="' . $rejectUrl . '" title="Reject"><i class="fa fa-times"></i> Reject</button>';
                        }

                        $deleteUrl = route('software.company-registration.destroy', $row->id);
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-request" data-url="' . $deleteUrl . '" title="Delete"><i class="fa fa-trash"></i></button>';

                        return $btn;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index', compact('columns', 'modules'));
        } catch (\Exception $e) {
            Log::error('CompanyRegistrationController index error: ' . $e->getMessage());
            return back()->withErrors($e->getMessage());
        }
    }

    public function approve($id)
    {
        try {
            $regRequest = CompanyRegistration::findOrFail($id);

            if ($regRequest->status === 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'This registration has already been approved.'
                ], 400);
            }

            DB::beginTransaction();

            $country = !empty($regRequest->country_id) ? MasterCountry::find($regRequest->country_id) : null;
            $planId = $regRequest->plan_id;
            if (empty($planId)) {
                $defaultPlan = PlanMaster::where('status', 'active')->first();
                $planId = $defaultPlan?->id;
            }
            $plan = !empty($planId) ? PlanMaster::find($planId) : null;

            $regCompName = trim($regRequest->company_name);
            $cleanRegCompName = preg_replace('/[^A-Za-z0-9]/', '', $regCompName);
            if (strlen($cleanRegCompName) < 3) {
                $cleanRegCompName = str_pad($cleanRegCompName, 3, 'X');
            }
            $regPrefix = ucfirst(strtolower(substr($cleanRegCompName, 0, 3)));

            $defaultValues = [
                'mobile_min' => 10,
                'mobile_max' => 10,
                'branch_type' => 'single',
                'status' => 'active',
                'employee_code_auto_generation' => 'auto',
                'phonecode' => $country?->code ?? '',
                'panel_url' => url('/software/login'),
                'app_key' => $regPrefix . '@' . date('Y'),
            ];

            if ($plan) {
                $defaultValues['max_employee_user_count'] = $plan->max_employee_user_count;
                $defaultValues['app_right'] = $plan->app_right;
                $defaultValues['panel_right'] = $plan->panel_right;
            }

            $companyData = array_merge([
                'gst_no' => $regRequest->gst_no,
                'company_name' => $regRequest->company_name,
                'person_name' => $regRequest->person_name,
                'whatsapp_number' => $regRequest->whatsapp_number,
                'email' => $regRequest->email,
                'password' => $regRequest->password,
                'sp' => $regRequest->sp,
                'country_id' => $regRequest->country_id,
                'state_id' => $regRequest->state_id,
                'city_id' => $regRequest->city_id,
                'plan_id' => $planId,
                'date_format' => $regRequest->date_format ?: Helper::getDefaultDateFormat(),
                'time_format' => $regRequest->time_format ?: Helper::getDefaultTimeFormat(),
                'hra_percentage' => $regRequest->hra_percentage ?? 40,
            ], $defaultValues);

            $company = Company::create($companyData);

            if ($company) {
                CompanyDetails::updateOrCreate(
                    ['company_id' => $company->id],
                    []
                );

                if (!empty($company->plan_id)) {
                    $plan_from = date('Y-m-d');
                    $plan_to = !empty($plan?->plan_valid_day) ? date('Y-m-d', strtotime('+' . $plan->plan_valid_day . ' days')) : date('Y-m-d', strtotime('+365 days'));

                    CompanySubscriptionPlan::create([
                        'company_id' => $company->id,
                        'plan_id' => $company->plan_id,
                        'plan_from' => $plan_from,
                        'plan_expiry_date' => $plan_to,
                        'subscription_status' => 'active',
                    ]);
                }

                $regRequest->update(['status' => 'approved']);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Company registration approved successfully! Company account created.'
                ]);
            }

            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create company from registration.'
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve registration error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $regRequest = CompanyRegistration::findOrFail($id);

            if ($regRequest->status === 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot reject an already approved registration.'
                ], 400);
            }

            $regRequest->update(['status' => 'rejected']);

            return response()->json([
                'status' => true,
                'message' => 'Company registration rejected.'
            ]);
        } catch (\Exception $e) {
            Log::error('Reject registration error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $regRequest = CompanyRegistration::findOrFail($id);
            $regRequest->delete();

            return response()->json([
                'status' => true,
                'message' => 'Registration deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete registration error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
