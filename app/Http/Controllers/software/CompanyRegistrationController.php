<?php

namespace App\Http\Controllers\software;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDetails;
use App\Models\CompanyRegistration;
use App\Models\CompanySubscriptionPlan;
use App\Models\Employee;
use App\Models\MasterCountry;
use App\Models\PlanMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class CompanyRegistrationController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Website Company Registration',
            'folder_path' => 'software.modules.company_registration',
            'route' => 'website-company-registration',
            'table_name' => (new CompanyRegistration())->getTable(),
            'permisstion_prefix' => 'website-company-registration',
            'module_name' => 'Website Company Registration'
        ];
    }

    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $modules['parent_type_id'] = ($this->authenticateLoginUserDetails?->parent_type_id) ? $this->authenticateLoginUserDetails?->parent_type_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        try {
            $modules['viewPermission'] = Gate::check('hasPermission', ['view', $modules['module_name']]);
            $modules['addPermission'] = Gate::check('hasPermission', ['add', $modules['module_name']]);
            $modules['editPermission'] = Gate::check('hasPermission', ['update', $modules['module_name']]);
            $modules['deletePermission'] = Gate::check('hasPermission', ['delete', $modules['module_name']]);

            if (!$modules['viewPermission']) {
                if ($request->ajax()) {
                    return $this->sendError('Unauthorized', [], [], 403);
                }
                return redirect()->route('software.dashboard')->withErrors('Unauthorized');
            }

            View::share('modules', $modules);

            $columns = [
                (object)['data' => 'company_name', 'name' => 'company_name', 'td_label' => 'Company Name', 'className' => 'w-20', 'orderable' => true, 'searchable' => true],
                (object)['data' => 'person_name', 'name' => 'person_name', 'td_label' => 'Person Details', 'className' => 'w-20 text-wrap', 'orderable' => true, 'searchable' => true],
                (object)['data' => 'register_type', 'name' => 'register_type', 'td_label' => 'Register Type', 'className' => 'text-center', 'orderable' => true, 'searchable' => true],
                (object)['data' => 'login_details', 'name' => 'whatsapp_number', 'td_label' => 'LOGIN DETAILS', 'className' => 'text-wrap text-left', 'orderable' => false, 'searchable' => false],
                (object)['data' => 'plan_name', 'name' => 'plan.title', 'td_label' => 'Plan Name', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object)['data' => 'created_at', 'name' => 'created_at', 'td_label' => 'Registered Date', 'className' => '', 'orderable' => true, 'searchable' => false],
                (object)['data' => 'status', 'name' => 'status', 'td_label' => 'STATUS', 'orderable' => true, 'searchable' => false, 'className' => 'text-center'],
                (object)['data' => 'action', 'name' => 'action', 'td_label' => 'ACTION', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
            ];

            View::share('columns', $columns);

            if ($request->ajax()) {
                if (!$modules['viewPermission']) {
                    return $this->sendError('Unauthorized', [], [], 403);
                }

                $data = CompanyRegistration::with(['plan', 'country', 'state', 'city', 'company'])
                    ->where(function ($query) {
                        if (Auth::guard('employees')->check()) {
                            $teamPersonCompanyId = Auth::guard('employees')->user()->company_id;
                            $company = Company::find($teamPersonCompanyId);
                            $query->where('company_id', $teamPersonCompanyId);
                            if ($company && $company->email) {
                                $query->orWhere('email', $company->email);
                            }
                        }
                    })
                    ->latest();

                if ($request->has('search') && !empty($request->search)) {
                    $searchTerm = $request->search;
                    $data->where(function ($q) use ($searchTerm) {
                        $q->where('company_name', 'like', "%{$searchTerm}%")
                            ->orWhere('person_name', 'like', "%{$searchTerm}%")
                            ->orWhere('whatsapp_number', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
                }

                if ($request->has('filter_plan') && !empty($request->filter_plan)) {
                    $data->where('plan_id', $request->filter_plan);
                }

                if ($request->has('register_type') && !empty($request->register_type) && $request->register_type !== 'all') {
                    $data->where('register_type', $request->register_type);
                }

                if ($request->has('status') && $request->status !== null && $request->status !== 'all') {
                    $reqStatus = strtolower($request->status);
                    if ($reqStatus === 'active') {
                        $data->where(function ($q) {
                            $q->whereIn('status', ['active', 'approved'])->orWhereNull('status');
                        });
                    } elseif ($reqStatus === 'inactive') {
                        $data->whereIn('status', ['inactive', 'rejected']);
                    } else {
                        $data->where('status', $request->status);
                    }
                }

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('company_details', function ($row) {
                        $html = '<strong>' . e($row->company_name) . '</strong><br>';
                        if (!empty($row->gst_no)) {
                            $html .= e($row->gst_no);
                        }
                        return $html;
                    })
                    ->addColumn('register_type', function ($row) {
                        $type = strtolower($row->register_type ?? '');
                        if (empty($type)) {
                            $type = ($row->company && $row->company->register_type) ? strtolower($row->company->register_type) : 'manual';
                        }
                        if ($type === 'google') {
                            return '<span class="badge bg-label-danger"><i class="fab fa-google me-1"></i> Google</span>';
                        } elseif ($type === 'admin') {
                            return '<span class="badge bg-label-primary"><i class="fa fa-user-shield me-1"></i> Admin</span>';
                        } else {
                            return '<span class="badge bg-label-info"><i class="fa fa-user-pen me-1"></i> Manual</span>';
                        }
                    })
                    ->addColumn('contact_info', function ($row) {
                        $html = '';

                        if (!empty($row->person_name)) {
                            $html .= '<i class="fa fa-user me-1 text-primary"></i>' . e($row->person_name);
                        }

                        if (!empty($row->whatsapp_number)) {
                            $wa_number = preg_replace('/[^0-9]/', '', $row->whatsapp_number);
                            $wa_link = "https://wa.me/{$wa_number}";
                            $html .= '<br><i class="fab fa-whatsapp text-success me-1"></i> <a href="' . $wa_link . '" target="_blank">' . e($row->whatsapp_number) . '</a>';
                        }

                        if (!empty($row->email)) {
                            $html .= '<br><i class="fa fa-envelope text-danger me-1"></i> <a href="mailto:' . e($row->email) . '">' . e($row->email) . '</a>';
                        }

                        return $html;
                    })
                    ->addColumn('login_details', function ($row) {
                        $company = $row->company ?? \App\Models\Company::where('email', $row->email)->first();
                        $appKey = $company?->app_key;
                        if (!$appKey) {
                            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $row->company_name ?? '');
                            if (strlen($cleanName) < 3) {
                                $cleanName = str_pad($cleanName, 3, 'X');
                            }
                            $prefix = ucfirst(strtolower(substr($cleanName, 0, 3)));
                            $appKey = $prefix . '@' . ($row->created_at ? $row->created_at->format('Y') : date('Y'));
                        }

                        $username = $row->whatsapp_number ?? '-';
                        $password = (string)($row->sp ?? '');

                        $html = '<div><strong>App Key:</strong> ' . e($appKey) . '</div>';
                        $html .= '<div><strong>Username:</strong> ' . e($username) . '</div>';
                        $html .= '<div><strong>Password:</strong> ' . e($password ?: '-') . '</div>';

                        $html .= '<button type="button" class="btn btn-xs btn-outline-primary copy-login-details mt-1" ';
                        $html .= 'data-app-key="' . e($appKey) . '" ';
                        $html .= 'data-username="' . e($username) . '" ';
                        $html .= 'data-password="' . e($password) . '" ';
                        $html .= 'title="Copy & Share">';
                        $html .= '<i class="ti ti-copy me-1"></i>Copy Detail</button>';

                        return $html;
                    })
                    ->addColumn('plan_info', function ($row) {
                        $company = $row->company ?? \App\Models\Company::where('email', $row->email)->first();
                        $companyId = $company?->id;

                        $planName = $row->plan?->title ?? $row->plan?->name ?? $company?->plan?->name ?? '-';

                        $html = '<strong>' . e($planName) . '</strong><br/>';

                        if ($companyId) {
                            $latestPlan = \App\Models\CompanySubscriptionPlan::where('company_id', $companyId)->orderBy('id', 'desc')->first();
                            $totalPurchasePlan = \App\Models\CompanySubscriptionPlan::where('company_id', $companyId)->count();

                            if (!empty($latestPlan?->plan_from)) {
                                $plan_from = \Carbon\Carbon::parse($latestPlan->plan_from)->format('d-m-Y');
                                $html .= '<strong>Plan From:</strong> ' . $plan_from . '<br/>';
                            }

                            if (!empty($latestPlan?->plan_expiry_date)) {
                                $plan_expiry_date = \Carbon\Carbon::parse($latestPlan->plan_expiry_date)->format('d-m-Y');
                                $html .= '<strong>Plan Expiry Date:</strong> ' . $plan_expiry_date . '<br/>';
                            }

                            if (!empty($latestPlan?->subscription_status)) {
                                $html .= '<strong>Subscription Status: ' . ucfirst($latestPlan->subscription_status) . '</strong><br/>';
                            }

                            if ($totalPurchasePlan > 0) {
                                $html .= '<strong>Total Purchase Plan: ' . $totalPurchasePlan . '</strong><br/>';
                            }
                        }

                        return $html;
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
                    })
                    ->editColumn('status', function ($row) use ($modules) {
                        $btn = '';
                        $company = $row->company ?? Company::where('email', $row->email)->first();
                        $companyId = $company?->id;
                        $latestPlan = $companyId ? CompanySubscriptionPlan::where('company_id', $companyId)->orderBy('id', 'desc')->first() : null;

                        $status = strtolower($row->status ?? 'active');
                        $updateStatusUrl = route('website-company-registration.status-update');

                        $dropdown = '<ul class="dropdown-menu">';
                        $btnExpired = '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-dark update-status" data-url="' . $updateStatusUrl . '" data-id="' . $row->id . '" data-update_status="expired">Expire</a></li>';
                        $btnActive = '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-success update-status" data-url="' . $updateStatusUrl . '" data-id="' . $row->id . '" data-update_status="active">Active</a></li>';
                        $btnInactive = '<li><a href="javascript:void(0)" class="dropdown-item waves-effect btn-label-danger update-status" data-url="' . $updateStatusUrl . '" data-id="' . $row->id . '" data-update_status="inactive">Inactive</a></li>';

                        if (!empty($latestPlan?->subscription_status) && $latestPlan?->subscription_status == 'expired') {
                            $btn .= '<button type="button" class="btn btn-dark btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">Expired</button>';
                            $dropdown .= $btnActive;
                            $dropdown .= $btnInactive;
                        } else {
                            if ($status == "active" || $status == "approved") {
                                $btn .= '<button type="button" class="btn btn-success btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">Active</button>';
                                $dropdown .= $btnInactive;
                                $dropdown .= $btnExpired;
                            } elseif ($status == "inactive" || $status == "rejected") {
                                $btn .= '<button type="button" class="btn btn-danger btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">Inactive</button>';
                                $dropdown .= $btnActive;
                                $dropdown .= $btnExpired;
                            } elseif ($status == "expired") {
                                $btn .= '<button type="button" class="btn btn-dark btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">Expired</button>';
                                $dropdown .= $btnActive;
                                $dropdown .= $btnInactive;
                            } else {
                                $btn .= '<button type="button" class="btn btn-success btn-sm dropdown-toggle waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">Active</button>';
                                $dropdown .= $btnInactive;
                                $dropdown .= $btnExpired;
                            }
                        }
                        $dropdown .= '</ul>';
                        $btn .= $dropdown;
                        return $btn;
                    })

                    ->addColumn('action', function ($row) {
                        $deleteUrl = Route::has('website-company-registration.destroy') ? route('website-company-registration.destroy', $row->id) : route('software.company-registration.destroy', $row->id);
                        
                        $companyId = $row->company?->id ?? Company::where('email', $row->email)->value('id');
                        $editUrl = $companyId ? route('company.edit', $companyId) : route('website-company-registration.edit', $row->id);

                        $btn = '<a href="' . $editUrl . '" class="btn btn-light btn-icon mx-1" title="Edit Company"><i class="fa-solid fa-pen-to-square"></i></a>';

                        $btn .= '
                            <div style="display:inline-block">
                                <button class="btn btn-icon waves-effect waves-light" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More options">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="' . $editUrl . '">
                                             <i class="tf-icons ti ti-edit"></i> Edit Company
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item view-company-modal-btn" href="javascript:void(0)" data-id="' . $row->id . '">
                                            <i class="tf-icons ti ti-eye"></i> View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item deletebutton text-danger" href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . $deleteUrl . '">
                                            <i class="tf-icons ti ti-trash"></i> Delete Registration
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        ';

                        return $btn;
                    })
                    ->rawColumns(['company_details', 'register_type', 'contact_info', 'login_details', 'plan_info', 'status', 'action'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index', compact('columns', 'modules'));
        } catch (\Exception $e) {
            Log::error('CompanyRegistrationController index error: ' . $e->getMessage());
            return redirect()->route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $regRequest = CompanyRegistration::find($id);
            if (!$regRequest) {
                return redirect()->route('software.dashboard')->withErrors('Matching company record not found to edit.');
            }

            if (Auth::guard('employees')->check()) {
                $user = Auth::guard('employees')->user();
                $isMasterAdmin = ($user->company_id == 1);
                $company = Company::find($user->company_id);
                $isOwn = ($regRequest->company_id && $regRequest->company_id == $user->company_id) || ($company && $company->email && $regRequest->email == $company->email);
                if (!$isMasterAdmin && !$isOwn) {
                    return redirect()->route('software.dashboard')->withErrors('Unauthorized');
                }
            }

            $company = Company::where('email', $regRequest->email)->first();
            if ($company) {
                return redirect()->route('company.edit', $company->id);
            }

            return redirect()->route('software.dashboard')->withErrors('Matching company record not found to edit.');
        } catch (\Exception $e) {
            return redirect()->route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function status_update(Request $request)
    {
        try {
            $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
            if (!$isMasterAdmin && !Gate::check('hasPermission', ['update', $this->modules['module_name']])) {
                return response()->json(['status' => false, 'message' => 'Unauthorized action.'], 403);
            }

            $id = $request->id;
            $status = $request->update_status ?? $request->status;
            $data = CompanyRegistration::find($id);
            if ($data) {
                if (Auth::guard('employees')->check() && !$isMasterAdmin) {
                    $user = Auth::guard('employees')->user();
                    $company = Company::find($user->company_id);
                    $isOwn = ($data->company_id && $data->company_id == $user->company_id) || ($company && $company->email && $data->email == $company->email);
                    if (!$isOwn) {
                        return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
                    }
                }

                $data->status = $status;
                $data->save();

                if ($data->email) {
                    $company = Company::where('email', $data->email)->first();
                    if ($company) {
                        if ($status == 'expired') {
                            $companySubscriptionPlan = CompanySubscriptionPlan::where('company_id', $company->id)
                                ->where('plan_id', $company->plan_id)
                                ->where('subscription_status', 'active')
                                ->orderBy('id', 'desc')
                                ->first();

                            if ($companySubscriptionPlan) {
                                $companySubscriptionPlan->subscription_status = 'expired';
                                $companySubscriptionPlan->plan_expiry_date = date('Y-m-d');
                                $companySubscriptionPlan->platform = 'manually';
                                $companySubscriptionPlan->save();
                            }
                        } else {
                            $company->status = $status;
                            $company->save();
                        }
                    }
                }

                return response()->json(['status' => true, 'message' => 'Status updated successfully.']);
            }
            return response()->json(['status' => false, 'message' => 'Record not found.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $data = CompanyRegistration::with(['plan', 'country', 'state', 'city', 'company'])->find($id);
            if ($data) {
                if (Auth::guard('employees')->check()) {
                    $user = Auth::guard('employees')->user();
                    $isMasterAdmin = ($user->company_id == 1);
                    $company = Company::find($user->company_id);
                    $isOwn = ($data->company_id && $data->company_id == $user->company_id) || ($company && $company->email && $data->email == $company->email);
                    if (!$isMasterAdmin && !$isOwn) {
                        return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
                    }
                }
                return response()->json(['status' => true, 'data' => $data]);
            }
            return response()->json(['status' => false, 'message' => 'Record not found.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);
            if (!$isMasterAdmin && !Gate::check('hasPermission', ['delete', $this->modules['module_name']])) {
                return response()->json(['status' => false, 'message' => 'Unauthorized action.'], 403);
            }

            $regRequest = CompanyRegistration::findOrFail($id);
            if (Auth::guard('employees')->check() && !$isMasterAdmin) {
                $user = Auth::guard('employees')->user();
                $company = Company::find($user->company_id);
                $isOwn = ($regRequest->company_id && $regRequest->company_id == $user->company_id) || ($company && $company->email && $regRequest->email == $company->email);
                if (!$isOwn) {
                    return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
                }
            }

            $email = $regRequest->email;

            if ($email) {
                $company = Company::where('email', $email)->first();
                if ($company) {
                    Employee::where('company_id', $company->id)->delete();
                    $company->delete();
                }
            }

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
