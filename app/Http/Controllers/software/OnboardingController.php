<?php

namespace App\Http\Controllers\software;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AssetsAllocationMaster;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeAsignAssets;
use App\Models\EmployeeType;
use App\Models\EmploymentDetail;
use App\Models\Onboarding;
use App\Models\OnboardingAsset;
use App\Models\OnboardingDocument;
use App\Models\OnboardingTraining;
use App\Models\Shift;
use App\Models\SubDepartment;
use App\Models\TeamRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class OnboardingController extends Controller
{
    public $modules = [];

    public function __construct()
    {
        parent::__construct();

        $this->modules = [
            'title' => 'Employee Onboarding',
            'folder_path' => 'software.modules.onboarding',
            'route' => 'onboarding',
            'table_name' => (new Onboarding())->getTable(),
            'permisstion_prefix' => 'onboarding',
            'module_name' => 'Employee Onboarding',
            'company_id' => ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;
        $loginUserId = ($modules['authLoginUserDetail'] && $modules['authLoginUserDetail']?->id) ? $modules['authLoginUserDetail']?->id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            if ($request->ajax()) {
                return response()->json(['data' => []], 403);
            }
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        $company_id = $modules['company_id'];

        if ($request->ajax()) {
            $data = Onboarding::with([
                'employee',
                'reportingManager',
                'branch',
                'department',
                'designation',
                'documents',
                'trainings',
                'assets'
            ]);

            if ($company_id) {
                $data = $data->where('company_id', $company_id);
            }

            if ($request->filled('status')) {
                $data = $data->where('status', $request->status);
            }

            if ($request->filled('department_id')) {
                $data = $data->where('department_id', $request->department_id);
            }

            if ($request->filled('branch_id')) {
                $data = $data->where('branch_id', $request->branch_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('candidate_info', function ($row) {
                    $fullName = $row->full_name ?: ($row->first_name . ' ' . $row->last_name);
                    $email = $row->email ?: '-';
                    $phone = $row->contact_number ?: '-';
                    $avatar = "https://ui-avatars.com/api/?name=" . urlencode($fullName) . "&background=0D8ABC&color=fff";

                    return '
                        <div class="d-flex justify-content-start align-items-center user-name">
                            <div class="avatar-wrapper">
                                <div class="avatar avatar-sm me-3">
                                    <img src="' . $avatar . '" alt="Avatar" class="rounded-circle">
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <a href="' . route('onboarding.show', $row->id) . '" class="text-heading text-truncate"><span class="fw-medium">' . e($fullName) . '</span></a>
                                <small class="text-muted">' . e($email) . ' | ' . e($phone) . '</small>
                            </div>
                        </div>';
                })
                ->addColumn('role_dept', function ($row) {
                    $dept = $row->department?->name ?? '-';
                    $desig = $row->designation?->name ?? '-';
                    $branch = $row->branch?->name ?? '-';
                    return '<div class="d-flex flex-column">
                                <span class="fw-medium">' . e($desig) . '</span>
                                <small class="text-muted">' . e($dept) . ' (' . e($branch) . ')</small>
                            </div>';
                })
                ->addColumn('manager', function ($row) {
                    if ($row->reportingManager) {
                        return e($row->reportingManager->full_name ?: ($row->reportingManager->first_name . ' ' . $row->reportingManager->last_name));
                    }
                    return '<span class="badge bg-label-secondary">Not Assigned</span>';
                })
                ->addColumn('progress_bar', function ($row) {
                    $progress = $row->progress_percentage;
                    $barColor = $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-primary' : 'bg-warning');
                    return '
                        <div class="d-flex align-items-center">
                            <div class="progress w-100 me-3" style="height: 8px;">
                                <div class="progress-bar ' . $barColor . '" role="progressbar" style="width: ' . $progress . '%;" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="fw-medium">' . $progress . '%</span>
                        </div>';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status == 'completed') {
                        return '<span class="badge bg-label-success">Completed</span>';
                    } elseif ($row->status == 'in_progress') {
                        return '<span class="badge bg-label-primary">In Progress (Step ' . $row->current_step . '/6)</span>';
                    } elseif ($row->status == 'draft') {
                        return '<span class="badge bg-label-secondary">Draft</span>';
                    } else {
                        return '<span class="badge bg-label-danger">' . ucfirst($row->status) . '</span>';
                    }
                })
                ->addColumn('action', function ($row) use ($modules) {
                    $btn = '<div class="d-flex align-items-center">';
                    $btn .= '<a href="' . route('onboarding.show', $row->id) . '" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" title="View & Process Onboarding"><i class="ti ti-eye"></i></a>';
                    if (!empty($modules['update_permission'])) {
                        $btn .= '<a href="' . route('onboarding.edit', $row->id) . '" class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" title="Edit"><i class="ti ti-pencil"></i></a>';
                    }
                    if (!empty($modules['delete_permission'])) {
                        $btn .= '<button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill waves-effect delete-record" data-id="' . $row->id . '" title="Delete"><i class="ti ti-trash"></i></button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['candidate_info', 'role_dept', 'manager', 'progress_bar', 'status_badge', 'action'])
                ->make(true);
        }

        // Summary KPI Stats
        $queryBase = Onboarding::query();
        if ($company_id) {
            $queryBase->where('company_id', $company_id);
        }
        $stats = [
            'total' => (clone $queryBase)->count(),
            'in_progress' => (clone $queryBase)->where('status', 'in_progress')->count(),
            'completed' => (clone $queryBase)->where('status', 'completed')->count(),
            'pending_docs' => OnboardingDocument::whereHas('onboarding', function ($q) use ($company_id) {
                if ($company_id) $q->where('company_id', $company_id);
            })->where('status', 'pending')->count(),
        ];

        $departments = Department::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $branches = Branch::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();

        return view($modules['folder_path'] . '.index', compact('modules', 'stats', 'departments', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['add_permission']) {
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        $company_id = $modules['company_id'];

        $branches = Branch::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $departments = Department::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $subDepartments = SubDepartment::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $designations = Designation::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $roles = TeamRole::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $shifts = Shift::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $employeeTypes = EmployeeType::when($company_id, fn($q) => $q->where('company_id', $company_id))->where('status', 'active')->get();
        $employees = Employee::when($company_id, fn($q) => $q->where('company_id', $company_id))->where('status', 'active')->get();

        return view($modules['folder_path'] . '.create', compact('modules', 'branches', 'departments', 'subDepartments', 'designations', 'roles', 'shifts', 'employeeTypes', 'employees'));
    }

    private function parseInputDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        try {
            $trimmed = trim($date);
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $trimmed)) {
                return Carbon::createFromFormat('d-m-Y', $trimmed)->format('Y-m-d');
            }
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $trimmed)) {
                return Carbon::createFromFormat('d/m/Y', $trimmed)->format('Y-m-d');
            }
            return Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company_id = $this->authenticateLoginUserDetails?->company_id ?? $request->company_id;

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:150',
            'contact_number' => 'required|string|max:20',
            'joining_date' => 'nullable',
            'branch_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'designation_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $fullName = trim($request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name);
            $onboardingCode = 'ONB-' . date('Y') . '-' . strtoupper(Str::random(5));

            $dob = $this->parseInputDate($request->date_of_birth);
            $joiningDate = $this->parseInputDate($request->joining_date) ?: Carbon::now()->format('Y-m-d');
            $probationMonths = $request->probation_period_months ?? 3;
            $probationEndDate = $this->parseInputDate($request->probation_end_date);
            if (!$probationEndDate && $joiningDate) {
                $probationEndDate = Carbon::parse($joiningDate)->addMonths($probationMonths)->format('Y-m-d');
            }

            $matchedRoleTemplate = Onboarding::getTemplateForDesignation($request->designation_id);
            $jobDescription = $request->filled('job_description') ? $request->job_description : $matchedRoleTemplate['job_description'];
            $attTarget = $request->filled('attendance_target_percentage') ? $request->attendance_target_percentage : $matchedRoleTemplate['attendance_target_percentage'];
            $lateTolerance = $request->filled('late_mark_tolerance') ? $request->late_mark_tolerance : $matchedRoleTemplate['late_mark_tolerance'];
            $workHours = $request->filled('daily_working_hours_target') ? $request->daily_working_hours_target : $matchedRoleTemplate['daily_working_hours_target'];

            $onboarding = Onboarding::create([
                'company_id' => $company_id,
                'employee_id' => $request->employee_id,
                'onboarding_code' => $onboardingCode,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'father_name' => $request->father_name,
                'last_name' => $request->last_name,
                'full_name' => $fullName,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'other_number' => $request->other_number,
                'date_of_birth' => $dob,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'marital_status' => $request->marital_status,
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,
                'branch_id' => $request->branch_id,
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'designation_id' => $request->designation_id,
                'role_id' => $request->role_id,
                'shift_id' => $request->shift_id,
                'reporting_manager_id' => $request->reporting_manager_id,
                'buddy_id' => $request->buddy_id,
                'joining_date' => $joiningDate,
                'probation_period_months' => $probationMonths,
                'probation_end_date' => $probationEndDate,
                'probation_status' => $request->probation_status ?? 'on_probation',
                'probation_notes' => $request->probation_notes,
                'employment_type' => $request->employment_type ?? 'full_time',
                'job_description' => $jobDescription,
                'kra_kpi_details' => $matchedRoleTemplate['kra_kpis'],
                'attendance_target_percentage' => $attTarget,
                'late_mark_tolerance' => $lateTolerance,
                'daily_working_hours_target' => $workHours,
                'status' => 'in_progress',
                'current_step' => 1,
                'progress_percentage' => 20,
                'created_by' => Auth::id(),
            ]);

            // Seed standard document checklist for this onboarding
            $defaultDocs = [
                ['type' => 'aadhar_card', 'title' => 'Aadhaar Card (Front & Back)'],
                ['type' => 'pan_card', 'title' => 'PAN Card'],
                ['type' => 'photo', 'title' => 'Passport Size Photograph'],
                ['type' => 'bank_passbook', 'title' => 'Bank Passbook / Cancelled Cheque'],
                ['type' => 'resume', 'title' => 'Resume / Curriculum Vitae'],
                ['type' => 'experience_letter', 'title' => 'Previous Relieving / Experience Letter'],
            ];

            foreach ($defaultDocs as $doc) {
                OnboardingDocument::create([
                    'onboarding_id' => $onboarding->id,
                    'employee_id' => $onboarding->employee_id,
                    'document_type' => $doc['type'],
                    'document_title' => $doc['title'],
                    'status' => 'pending',
                ]);
            }

            // Seed default 3 Induction Training Modules (as requested by user)
            $defaultTrainings = [
                [
                    'module_number' => 1,
                    'title' => 'Induction Module 1: Company Overview & Culture',
                    'description' => 'Introduction to company history, values, vision, code of conduct, and organizational structure.',
                ],
                [
                    'module_number' => 2,
                    'title' => 'Induction Module 2: HR Policies & Workplace Safety',
                    'description' => 'Guidelines on leave rules, attendance policies, workplace ethics, POSH guidelines, and health & safety.',
                ],
                [
                    'module_number' => 3,
                    'title' => 'Induction Module 3: Job Role, Tools & Process Training',
                    'description' => 'Departmental workflow, software tools, key performance indicators (KPIs), and initial milestones.',
                ],
            ];

            foreach ($defaultTrainings as $training) {
                OnboardingTraining::create([
                    'onboarding_id' => $onboarding->id,
                    'module_number' => $training['module_number'],
                    'title' => $training['title'],
                    'description' => $training['description'],
                    'status' => 'pending',
                ]);
            }

            // Seed standard asset checklist (Mobile, T-Shirt, Shoes, Laptop, ID Card)
            $defaultAssets = [
                ['type' => 'mobile', 'name' => 'Company Mobile Phone / SIM', 'spec' => 'SIM / Mobile Number'],
                ['type' => 'laptop', 'name' => 'Laptop / Desktop PC', 'spec' => 'System / Serial No.'],
                ['type' => 'tshirt', 'name' => 'Company Uniform / T-Shirt', 'spec' => 'Size: M / L / XL'],
                ['type' => 'shoes', 'name' => 'Safety / Work Shoes', 'spec' => 'Size: 7 / 8 / 9 / 10'],
                ['type' => 'id_card', 'name' => 'Employee ID Card & Access Badge', 'spec' => 'Lanyard & Badge'],
            ];

            foreach ($defaultAssets as $asset) {
                OnboardingAsset::create([
                    'onboarding_id' => $onboarding->id,
                    'employee_id' => $onboarding->employee_id,
                    'asset_type' => $asset['type'],
                    'asset_name' => $asset['name'],
                    'specification_or_size' => $asset['spec'],
                    'status' => 'pending',
                ]);
            }

            $onboarding->calculateProgress();

            DB::commit();

            return Redirect::route('onboarding.show', $onboarding->id)->with('success', 'Onboarding initiated successfully for ' . $fullName);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with('error', 'Error initiating onboarding: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource with multi-step interactive tabs.
     */
    public function show($id, $tab = 'basic-details')
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (!$modules['view_permission']) {
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        $company_id = $modules['company_id'];

        $onboarding = Onboarding::with([
            'employee',
            'reportingManager',
            'buddy',
            'branch',
            'department',
            'subDepartment',
            'designation',
            'role',
            'shift',
            'documents',
            'trainings',
            'assets'
        ])->findOrFail($id);

        $branches = Branch::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $departments = Department::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $subDepartments = SubDepartment::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $designations = Designation::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $roles = TeamRole::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $shifts = Shift::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $employeeTypes = EmployeeType::when($company_id, fn($q) => $q->where('company_id', $company_id))->where('status', 'active')->get();
        $employees = Employee::when($company_id, fn($q) => $q->where('company_id', $company_id))->where('status', 'active')->get();
        $assetMasters = AssetsAllocationMaster::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $company = Company::find($onboarding->company_id ?: $company_id);
        $is_view_only = true;
        $validTabs = ['basic-details', 'documents', 'jd-kra', 'trainings', 'assets', 'reporting'];
        $active_tab = in_array($tab, $validTabs) ? $tab : 'basic-details';

        return view($modules['folder_path'] . '.show', compact(
            'modules',
            'onboarding',
            'branches',
            'departments',
            'subDepartments',
            'designations',
            'roles',
            'shifts',
            'employeeTypes',
            'employees',
            'assetMasters',
            'company',
            'is_view_only',
            'active_tab'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, $tab = 'basic-details')
    {
        $modules = $this->modules;
        $modules['authLoginUserDetail'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails : null;
        $modules['company_id'] = ($this->authenticateLoginUserDetails) ? $this->authenticateLoginUserDetails?->company_id : null;

        if (count(config('constants.permissions'))) {
            foreach (config('constants.permissions') as $key => $value) {
                $modules[$value . '_permission'] = (isset($modules['company_id']) && !$modules['company_id'])
                    ? true
                    : Gate::check('hasPermission', [$value, $modules['module_name']]);
            }
        }

        if (empty($modules['update_permission'])) {
            abort(403, 'Unauthorized');
        }

        View::share('modules', $modules);

        $company_id = $modules['company_id'];

        $onboarding = Onboarding::with([
            'employee',
            'reportingManager',
            'buddy',
            'branch',
            'department',
            'subDepartment',
            'designation',
            'role',
            'shift',
            'documents',
            'trainings',
            'assets'
        ])->findOrFail($id);

        $branches = Branch::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $departments = Department::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $subDepartments = SubDepartment::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $designations = Designation::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $roles = TeamRole::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $shifts = Shift::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $employeeTypes = EmployeeType::when($company_id, fn($q) => $q->where('company_id', $company_id))->where('status', 'active')->get();
        $employees = Employee::when($company_id, fn($q) => $q->where('company_id', $company_id))->where('status', 'active')->get();
        $assetMasters = AssetsAllocationMaster::when($company_id, fn($q) => $q->where('company_id', $company_id))->get();
        $company = Company::find($onboarding->company_id ?: $company_id);
        $is_view_only = false;
        $validTabs = ['basic-details', 'documents', 'jd-kra', 'trainings', 'assets', 'reporting'];
        $active_tab = in_array($tab, $validTabs) ? $tab : 'basic-details';

        return view($modules['folder_path'] . '.show', compact(
            'modules',
            'onboarding',
            'branches',
            'departments',
            'subDepartments',
            'designations',
            'roles',
            'shifts',
            'employeeTypes',
            'employees',
            'assetMasters',
            'company',
            'is_view_only',
            'active_tab'
        ));
    }

    /**
     * Update Step 1 (Basic Details)
     */
    public function updateBasicDetails(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'contact_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $dob = $this->parseInputDate($request->date_of_birth);
            $joiningDate = $this->parseInputDate($request->joining_date);
            $probationEndDate = $this->parseInputDate($request->probation_end_date);
            if (!$probationEndDate && $joiningDate && $request->filled('probation_period_months')) {
                $probationEndDate = Carbon::parse($joiningDate)->addMonths((int)$request->probation_period_months)->format('Y-m-d');
            }

            $fullName = trim($request->first_name . ' ' . ($request->middle_name ? $request->middle_name . ' ' : '') . $request->last_name);

            $onboarding->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'father_name' => $request->father_name,
                'last_name' => $request->last_name,
                'full_name' => $fullName,
                'email' => $request->email,
                'contact_number' => $request->contact_number,
                'other_number' => $request->other_number,
                'date_of_birth' => $dob,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'marital_status' => $request->marital_status,
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,
                'branch_id' => $request->branch_id,
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'designation_id' => $request->designation_id,
                'role_id' => $request->role_id,
                'shift_id' => $request->shift_id,
                'joining_date' => $joiningDate,
                'probation_period_months' => $request->probation_period_months,
                'probation_end_date' => $probationEndDate,
                'probation_status' => $request->probation_status ?? 'on_probation',
                'probation_notes' => $request->probation_notes,
                'employment_type' => $request->employment_type,
                'job_description' => $request->filled('job_description') ? $request->job_description : $onboarding->job_description,
                'attendance_target_percentage' => $request->filled('attendance_target_percentage') ? $request->attendance_target_percentage : $onboarding->attendance_target_percentage,
                'late_mark_tolerance' => $request->filled('late_mark_tolerance') ? $request->late_mark_tolerance : $onboarding->late_mark_tolerance,
                'daily_working_hours_target' => $request->filled('daily_working_hours_target') ? $request->daily_working_hours_target : $onboarding->daily_working_hours_target,
                'current_step' => max($onboarding->current_step, 2),
            ]);

            $onboarding->calculateProgress();

            return response()->json([
                'status' => true,
                'message' => 'Basic details updated successfully!',
                'progress' => $onboarding->progress_percentage
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload / Update Document (Step 2)
     */
    public function uploadDocument(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'document_id' => 'nullable|integer',
            'document_type' => 'required|string',
            'document_number' => 'nullable|string|max:100',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $doc = null;
            if ($request->document_id) {
                $doc = OnboardingDocument::where('onboarding_id', $onboarding->id)->where('id', $request->document_id)->first();
            }

            if (!$doc) {
                $doc = new OnboardingDocument();
                $doc->onboarding_id = $onboarding->id;
                $doc->employee_id = $onboarding->employee_id;
                $doc->document_type = $request->document_type;
                $doc->document_title = $request->document_title ?: ucfirst(str_replace('_', ' ', $request->document_type));
            }

            if ($request->filled('document_number')) {
                $doc->document_number = $request->document_number;
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $company = $onboarding->company ?: \App\Models\Company::find($onboarding->company_id);
                $companySlug = \Illuminate\Support\Str::slug(($company?->id ?? $onboarding->company_id ?? '1') . ' ' . ($company?->company_name ?? 'company'));
                $empNameSlug = \Illuminate\Support\Str::slug($onboarding->id . ' ' . ($onboarding->full_name ?: ($onboarding->first_name . ' ' . $onboarding->last_name)));
                $folder = "uploads/" . $companySlug . "/employee/onboarding/" . $empNameSlug . "/documents/";
                $uploadDir = public_path($folder);
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $doc->file_path = $folder . $filename;
                $doc->file_name = $file->getClientOriginalName();
                $doc->status = 'uploaded';
            }

            $doc->save();

            $onboarding->current_step = max($onboarding->current_step, 2);
            $onboarding->calculateProgress();

            return response()->json([
                'status' => true,
                'message' => 'Document saved successfully!',
                'doc_id' => $doc->id,
                'doc_status' => $doc->status,
                'file_name' => $doc->file_name,
                'progress' => $onboarding->progress_percentage
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload Company Employee Handbook PDF
     */
    public function uploadHandbook(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'handbook_file' => 'required|file|mimes:pdf|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $file = $request->file('handbook_file');
            $company = $onboarding->company ?: \App\Models\Company::find($onboarding->company_id);
            $companySlug = \Illuminate\Support\Str::slug(($company?->id ?? $onboarding->company_id ?? '1') . ' ' . ($company?->company_name ?? 'company'));
            $folder = "uploads/" . $companySlug . "/handbook/";
            $uploadDir = public_path($folder);
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = "handbook.pdf";
            $file->move($uploadDir, $filename);

            return response()->json([
                'status' => true,
                'message' => 'Company Employee Handbook uploaded successfully!',
                'file_url' => asset($folder . $filename) . '?v=' . time(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add Custom Document checklist item
     */
    public function addDocumentChecklist(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'document_title' => 'required|string|max:150',
            'document_type' => 'required|string|max:50',
            'document_number' => 'nullable|string|max:100',
            'file' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $doc = new OnboardingDocument();
            $doc->onboarding_id = $onboarding->id;
            $doc->employee_id = $onboarding->employee_id;
            $doc->document_type = $request->document_type;
            $doc->document_title = $request->document_title;
            $doc->document_number = $request->document_number;
            $doc->status = 'pending';

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $company = $onboarding->company ?: \App\Models\Company::find($onboarding->company_id);
                $companySlug = \Illuminate\Support\Str::slug(($company?->id ?? $onboarding->company_id ?? '1') . ' ' . ($company?->company_name ?? 'company'));
                $empNameSlug = \Illuminate\Support\Str::slug($onboarding->id . ' ' . ($onboarding->full_name ?: ($onboarding->first_name . ' ' . $onboarding->last_name)));
                $folder = "uploads/" . $companySlug . "/employee/onboarding/" . $empNameSlug . "/documents/";
                $uploadDir = public_path($folder);
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = time() . '_' . \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $doc->file_path = $folder . $filename;
                $doc->file_name = $file->getClientOriginalName();
                $doc->status = 'uploaded';
            }

            $doc->save();
            $onboarding->calculateProgress();

            return response()->json([
                'status' => true,
                'message' => 'Custom document added successfully!',
                'progress' => $onboarding->progress_percentage
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify / Reject Document
     */
    public function verifyDocument(Request $request, $id, $docId = null)
    {
        $docId = $docId ?: $request->document_id;
        $onboarding = Onboarding::findOrFail($id);
        $doc = OnboardingDocument::where('onboarding_id', $onboarding->id)->where('id', $docId)->firstOrFail();

        $action = $request->action; // 'verified' or 'rejected'
        if ($action == 'verified') {
            $doc->status = 'verified';
            $doc->verified_by = Auth::id();
            $doc->verified_at = Carbon::now();
            $doc->rejection_reason = null;
        } else {
            $doc->status = 'rejected';
            $doc->rejection_reason = $request->rejection_reason ?: 'Document rejected by HR verification.';
            $doc->verified_by = Auth::id();
            $doc->verified_at = Carbon::now();
        }
        $doc->save();

        $onboarding->calculateProgress();

        return response()->json([
            'status' => true,
            'message' => 'Document status updated to ' . ucfirst($doc->status),
            'doc_status' => $doc->status,
            'progress' => $onboarding->progress_percentage
        ]);
    }

    /**
     * Update Step 3 (Company Overview, Job Description & KRA/KPI)
     */
    public function updateCompanyOverview(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $onboarding->company_overview_acknowledged = $request->has('company_overview_acknowledged') ? (bool)$request->company_overview_acknowledged : true;
        $onboarding->company_overview_acknowledged_at = Carbon::now();
        $onboarding->company_overview_notes = $request->company_overview_notes;

        // Job Description & KRA/KPI fields
        if ($request->filled('job_description')) {
            $onboarding->job_description = $request->job_description;
        }
        if ($request->filled('attendance_target_percentage')) {
            $onboarding->attendance_target_percentage = $request->attendance_target_percentage;
        }
        if ($request->filled('late_mark_tolerance')) {
            $onboarding->late_mark_tolerance = $request->late_mark_tolerance;
        }
        if ($request->filled('daily_working_hours_target')) {
            $onboarding->daily_working_hours_target = $request->daily_working_hours_target;
        }
        if ($request->has('jd_acknowledged')) {
            $onboarding->jd_acknowledged = (bool)$request->jd_acknowledged;
            $onboarding->jd_acknowledged_at = Carbon::now();
        }
        if ($request->has('kra_kpi_json')) {
            $decoded = json_decode($request->kra_kpi_json, true);
            if (is_array($decoded)) {
                $onboarding->kra_kpi_details = $decoded;
            }
        }

        $onboarding->current_step = max($onboarding->current_step, 4);
        $onboarding->save();

        $onboarding->calculateProgress();

        return response()->json([
            'status' => true,
            'message' => 'Job Description, KRA/KPI & Orientation saved successfully!',
            'progress' => $onboarding->progress_percentage
        ]);
    }

    /**
     * Step 4: Add / Update / Status Toggle for Training Modules
     */
    public function updateTrainingStatus(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        if ($request->has('training_id')) {
            $training = OnboardingTraining::where('onboarding_id', $onboarding->id)->where('id', $request->training_id)->firstOrFail();

            if ($request->filled('status')) {
                $training->status = $request->status;
                if ($request->status == 'completed') {
                    $training->completed_at = Carbon::now();
                } else {
                    $training->completed_at = null;
                }
            }

            if ($request->filled('trainer_notes')) {
                $training->trainer_notes = $request->trainer_notes;
            }

            if ($request->hasFile('pdf_file')) {
                $file = $request->file('pdf_file');
                $company = $onboarding->company ?: \App\Models\Company::find($onboarding->company_id);
                $companySlug = \Illuminate\Support\Str::slug(($company?->id ?? $onboarding->company_id ?? '1') . ' ' . ($company?->company_name ?? 'company'));
                $empNameSlug = \Illuminate\Support\Str::slug($onboarding->id . ' ' . ($onboarding->full_name ?: ($onboarding->first_name . ' ' . $onboarding->last_name)));
                $folder = "uploads/" . $companySlug . "/employee/onboarding/" . $empNameSlug . "/training/";
                $uploadDir = public_path($folder);
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = time() . '_module_' . $training->module_number . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $training->pdf_file_path = $folder . $filename;
                $training->pdf_file_name = $file->getClientOriginalName();
            }

            $training->save();
        } elseif ($request->filled('new_title')) {
            // Add custom training module
            $maxModule = OnboardingTraining::where('onboarding_id', $onboarding->id)->max('module_number') ?? 0;
            $training = new OnboardingTraining();
            $training->onboarding_id = $onboarding->id;
            $training->module_number = $maxModule + 1;
            $training->title = $request->new_title;
            $training->description = $request->new_description;
            $training->status = 'pending';

            if ($request->hasFile('pdf_file')) {
                $file = $request->file('pdf_file');
                $company = $onboarding->company ?: \App\Models\Company::find($onboarding->company_id);
                $companySlug = \Illuminate\Support\Str::slug(($company?->id ?? $onboarding->company_id ?? '1') . ' ' . ($company?->company_name ?? 'company'));
                $empNameSlug = \Illuminate\Support\Str::slug($onboarding->id . ' ' . ($onboarding->full_name ?: ($onboarding->first_name . ' ' . $onboarding->last_name)));
                $folder = "uploads/" . $companySlug . "/employee/onboarding/" . $empNameSlug . "/training/";
                $uploadDir = public_path($folder);
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = time() . '_module_' . $training->module_number . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                $training->pdf_file_path = $folder . $filename;
                $training->pdf_file_name = $file->getClientOriginalName();
            }

            $training->save();
        }

        $onboarding->current_step = max($onboarding->current_step, 5);
        $onboarding->calculateProgress();

        return response()->json([
            'status' => true,
            'message' => 'Induction Training status updated successfully!',
            'progress' => $onboarding->progress_percentage
        ]);
    }

    /**
     * Step 5: Assign / Update Assets (Mobile, Laptop, Uniform, Shoes)
     */
    public function assignAsset(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'asset_id' => 'nullable|integer',
            'asset_type' => 'required|string',
            'asset_name' => 'required|string|max:150',
            'specification_or_size' => 'nullable|string|max:100',
            'asset_code_or_serial' => 'nullable|string|max:100',
            'issued_date' => 'nullable|date',
            'status' => 'required|string|in:pending,assigned,handed_over',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $asset = null;
        if ($request->asset_id) {
            $asset = OnboardingAsset::where('onboarding_id', $onboarding->id)->where('id', $request->asset_id)->first();
        }

        if (!$asset) {
            $asset = new OnboardingAsset();
            $asset->onboarding_id = $onboarding->id;
            $asset->employee_id = $onboarding->employee_id;
        }

        $asset->asset_type = $request->asset_type;
        $asset->asset_name = $request->asset_name;
        $asset->specification_or_size = $request->specification_or_size;
        $asset->asset_code_or_serial = $request->asset_code_or_serial;
        $asset->issued_date = $request->issued_date ?: Carbon::now();
        $asset->status = $request->status;
        $asset->notes = $request->notes;
        $asset->save();

        $onboarding->current_step = max($onboarding->current_step, 6);
        $onboarding->calculateProgress();

        return response()->json([
            'status' => true,
            'message' => 'Asset allocation updated successfully!',
            'asset' => $asset,
            'progress' => $onboarding->progress_percentage
        ]);
    }

    /**
     * Step 6: Assign Reporting Manager & Team / Finalize Onboarding
     */
    public function assignReportingAndFinalize(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reporting_manager_id' => 'required|integer',
            'buddy_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'designation_id' => 'nullable|integer',
            'shift_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $onboarding->reporting_manager_id = $request->reporting_manager_id;
            $onboarding->buddy_id = $request->buddy_id;
            if ($request->filled('branch_id')) $onboarding->branch_id = $request->branch_id;
            if ($request->filled('department_id')) $onboarding->department_id = $request->department_id;
            if ($request->filled('sub_department_id')) $onboarding->sub_department_id = $request->sub_department_id;
            if ($request->filled('designation_id')) $onboarding->designation_id = $request->designation_id;
            if ($request->filled('shift_id')) $onboarding->shift_id = $request->shift_id;
            $onboarding->remarks = $request->remarks;

            // If an employee is already linked or can be created / synced
            if ($onboarding->employee_id) {
                $emp = Employee::find($onboarding->employee_id);
                if ($emp) {
                    $emp->parent_id = $request->reporting_manager_id;
                    if ($request->filled('branch_id')) $emp->branch_id = $request->branch_id;
                    $emp->save();

                    // Sync or create EmploymentDetail
                    $employment = EmploymentDetail::firstOrNew([
                        'employee_id' => $emp->id,
                        'company_id' => $emp->company_id,
                    ]);
                    $employment->designation_type = $employment->designation_type ?: 'employee';
                    $employment->department_id = $onboarding->department_id ?: ($employment->department_id ?: 1);
                    $employment->sub_department_id = $onboarding->sub_department_id ?: $employment->sub_department_id;
                    $employment->designation_id = $onboarding->designation_id ?: ($employment->designation_id ?: 1);
                    $employment->shift = $onboarding->shift_id ?: ($employment->shift ?: 1);
                    $employment->date_of_joining = $onboarding->joining_date ? $onboarding->joining_date->format('Y-m-d') : ($employment->date_of_joining ?: Carbon::now()->format('Y-m-d'));
                    $employment->employment_confirmation_date = $onboarding->probation_end_date ? $onboarding->probation_end_date->format('Y-m-d') : $employment->employment_confirmation_date;
                    $employment->payment_mode = $employment->payment_mode ?: 'NEFT';
                    $employment->employment_type = $onboarding->employment_type ?: ($employment->employment_type ?: 1);
                    $employment->outdoor_attendance = $employment->outdoor_attendance ?: 'no';
                    $employment->status = 'active';
                    $employment->created_by = $employment->created_by ?: Auth::id();
                    $employment->save();
                }
            }

            if ($request->boolean('mark_complete')) {
                $onboarding->status = 'completed';
                $onboarding->completed_at = Carbon::now();
                $onboarding->completed_by = Auth::id();
                $onboarding->progress_percentage = 100;
            } else {
                $onboarding->calculateProgress();
            }

            $onboarding->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Reporting Manager assigned and onboarding profile finalized successfully!',
                'progress' => $onboarding->progress_percentage,
                'onboarding_status' => $onboarding->status
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Convert / Create Full Employee record from Onboarding candidate
     */
    public function convertToEmployee(Request $request, $id)
    {
        $onboarding = Onboarding::findOrFail($id);

        if ($onboarding->employee_id && Employee::find($onboarding->employee_id)) {
            return response()->json([
                'status' => true,
                'message' => 'Employee is already linked to this onboarding record.',
                'employee_id' => $onboarding->employee_id
            ]);
        }

        try {
            DB::beginTransaction();

            $company_id = $onboarding->company_id ?: Auth::user()?->company_id;

            // Auto generate employee code
            $lastEmp = Employee::where('company_id', $company_id)->orderBy('id', 'desc')->first();
            $nextNum = ($lastEmp ? ($lastEmp->id + 1) : 1);
            $empCode = 'EMP' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Aadhar & PAN from documents
            $aadharDoc = $onboarding->documents()->where('document_type', 'aadhar_card')->first();
            $panDoc = $onboarding->documents()->where('document_type', 'pan_card')->first();

            $employee = Employee::create([
                'company_id' => $company_id,
                'branch_id' => $onboarding->branch_id,
                'parent_id' => $onboarding->reporting_manager_id,
                'employee_code' => $empCode,
                'first_name' => $onboarding->first_name,
                'middle_name' => $onboarding->middle_name,
                'father_name' => $onboarding->father_name,
                'full_name' => $onboarding->full_name,
                'username' => strtolower($empCode),
                'password' => Hash::make('password123'),
                'sp' => 'password123',
                'role_id' => $onboarding->role_id,
                'email' => $onboarding->email,
                'contact_number' => $onboarding->contact_number,
                'other_number' => $onboarding->other_number,
                'date_of_birth' => $onboarding->date_of_birth,
                'gender' => $onboarding->gender,
                'blood_group' => $onboarding->blood_group,
                'marital_status' => $onboarding->marital_status,
                'current_address' => $onboarding->current_address,
                'permanent_address' => $onboarding->permanent_address,
                'aadhar_card_number' => $aadharDoc?->document_number,
                'pan_card_number' => $panDoc?->document_number,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            // Create Employment Detail
            EmploymentDetail::create([
                'company_id' => $company_id,
                'employee_id' => $employee->id,
                'designation_type' => 'employee',
                'department_id' => $onboarding->department_id ?: 1,
                'sub_department_id' => $onboarding->sub_department_id,
                'designation_id' => $onboarding->designation_id ?: 1,
                'shift' => $onboarding->shift_id ?: 1,
                'date_of_joining' => $onboarding->joining_date ? $onboarding->joining_date->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                'employment_confirmation_date' => $onboarding->probation_end_date ? $onboarding->probation_end_date->format('Y-m-d') : null,
                'payment_mode' => 'NEFT',
                'employment_type' => $onboarding->employment_type ?: 1,
                'outdoor_attendance' => 'no',
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            // Link employee_id to onboarding
            $onboarding->employee_id = $employee->id;
            $onboarding->save();

            // Sync assets into EmployeeAsignAssets table
            foreach ($onboarding->assets()->whereIn('status', ['assigned', 'handed_over'])->get() as $asset) {
                EmployeeAsignAssets::create([
                    'company_id' => $company_id,
                    'employee_id' => $employee->id,
                    'assets_id' => $asset->assets_allocation_master_id ?: 1,
                    'date' => $asset->issued_date ? $asset->issued_date->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                    'reference_no' => $asset->asset_code_or_serial ?: 'ONB-' . $asset->id,
                    'descrption' => $asset->asset_name . ' (' . ($asset->specification_or_size ?: '') . ')',
                    'status' => 'active',
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Employee successfully activated in OceanHR! Code: ' . $empCode,
                'employee_code' => $empCode,
                'employee_id' => $employee->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fetch role-specific Job Description & KRA/KPI template data
     */
    public function getRoleTemplate(Request $request, $key)
    {
        $templates = Onboarding::getRoleTemplates();

        if (isset($templates[$key])) {
            $template = $templates[$key];
        } else {
            $template = Onboarding::getTemplateForDesignation($key);
        }

        return response()->json([
            'status' => true,
            'template' => $template,
            'all_templates' => $templates,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $onboarding = Onboarding::findOrFail($id);
            $onboarding->deleted_by = Auth::id();
            $onboarding->save();
            $onboarding->delete();

            return response()->json(['status' => true, 'message' => 'Onboarding record deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
