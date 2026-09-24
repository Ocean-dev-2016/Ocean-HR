<?php

namespace App\Http\Controllers;

use App\Http\Controllers\software\ExpenseCategoryController;
use App\Models\AssetsAllocationMaster;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Facades\Http;
use App\Models\ExpenseCategory;
use App\Models\MasterArea;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use App\Models\MasterState;
use App\Models\Designation;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\TeamRole;
use App\Models\PlanMaster;
use App\Models\CompanySubscriptionPlan;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\EmployeeType;
use App\Models\LoanTypes;
use App\Models\Process;
use App\Models\SubDepartment;
use App\Models\EmployeeWiseSalaryDetail;
use App\Models\Attendance;
use App\Models\Bonus;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class CommonController extends Controller
{
    public function __construct(Request $request) {}

    public function get_country(Request $request)
    {
        try {
            // $data = MasterCountry::orderBy('name', 'ASC')->get();
            $data = MasterCountry::query();

            if ($request?->filter_by_status && $request?->filter_by_status != "all") {
                $data = $data->where('status', $request?->filter_by_status);
            }
            $data = $data->orderBy('name', 'ASC')->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->name . "";
                $temp['code'] = $row?->code . "";
                $temp['short_name'] = $row?->short_name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Country list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_state(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'country_id' => [
                    'required',
                    Rule::exists((new MasterCountry())->getTable(), 'id')
                ]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            if (!$request?->country_id) {
                return $this->sendError("Country id is required.");
            }
            $data = MasterState::where('country_id', $request?->country_id)->get();
            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->name . "";
                $temp['country_id'] = $row?->country_id . "";
                $temp['country_name'] = $row?->country?->name . "";
                return $temp;
            });
            return $this->sendResponse($data, "State list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_city(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'country_id' => [
                'required',
                Rule::exists((new MasterCountry())->getTable(), 'id')
            ],
            'state_id' => [
                'required',
                Rule::exists((new MasterState())->getTable(), 'id')
            ]
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            if (!$request?->country_id) {
                return $this->sendError("Country id is required.");
            }
            if (!$request?->state_id) {
                return $this->sendError("State id is required.");
            }
            $data = MasterCity::where('country_id', $request?->country_id)->where('state_id', $request?->state_id)->orderBy('name', 'asc')->get();
            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->name . "";
                $temp['state_id'] = $row?->state_id . "";
                $temp['state_name'] = $row?->state?->name . "";
                $temp['country_id'] = $row?->country_id . "";
                $temp['country_name'] = $row?->country?->name . "";
                return $temp;
            });
            return $this->sendResponse($data, "City list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_area(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'country_id' => [
                'required',
                Rule::exists((new MasterCountry())->getTable(), 'id')
            ],
            'state_id' => [
                'required',
                Rule::exists((new MasterState())->getTable(), 'id')
            ],
            'city_id' => [
                'required',
                Rule::exists((new MasterCity())->getTable(), 'id')
            ]
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {

            $data = MasterArea::query();
            $data = $data->where('company_id', $request?->company_id);
            $data = $data->where('country_id', $request?->country_id);
            $data = $data->where('state_id', $request?->state_id);
            $data = $data->where('city_id', $request?->city_id);
            $data = $data->orderBy('area_name', 'asc')->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['area_name'] = $row?->area_name . "";

                $temp['company_id'] = $row?->company_id . "";
                $temp['company_name'] = $row?->company?->company_name . "";

                $temp['city_id'] = $row?->city_id . "";
                $temp['city_name'] = $row?->city?->name . "";

                $temp['state_id'] = $row?->state_id . "";
                $temp['state_name'] = $row?->state?->name . "";

                $temp['country_id'] = $row?->country_id . "";
                $temp['country_name'] = $row?->country?->name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Area list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /** API Get Company */
    public function get_companies(Request $request)
    {

        try {

            $data = new Company();
            $data = $data->with(['plan', 'country', 'state', 'city']);
            if ($request?->filter_by_status) {
                if ($request?->filter_by_status != "all") {
                    $data = $data->where('status', $request?->filter_by_status);
                }
            } else {
                $data = $data->where('status', 'active');
            }
            $data = $data->orderBy('company_name', 'asc');
            $data = $data->get();

            $data = $data->map(function ($row) use ($request) {
                $unsetVariablle = ["app_right", "panel_right", 'password', 'panel_url', 'sp', 'database_name', 'database_password', 'database_user', 'default_password', 'otp', 'reset_password', 'created_by', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'];

                $temp = $row->toArray();
                foreach ($unsetVariablle as $key => $value) {
                    if (isset($temp[$value])) {
                        unset($temp[$value]);
                    }
                }
                // dd("L-253", $temp);
                // $temp = $row;
                if ($request?->full_detail) {
                    return $temp;
                    $temp = $row->toArray();
                }

                $temp['id'] = $row?->id . "";
                $temp['company_name'] = $row?->company_name . "";
                $temp['max_employee_user_count'] = $row?->max_employee_user_count . "";
                $temp['phonecode'] = $row?->phonecode . "";
                $temp['mobile_min'] = $row?->mobile_min . "";
                $temp['mobile_max'] = $row?->mobile_max . "";
                $temp['hra_percentage'] = $row?->hra_percentage !== null ? (float) $row->hra_percentage : null;
                $temp['branch_type'] = $row?->branch_type . "";
                $temp['gst_no'] = $row?->gst_no . "";
                return $temp;
            });
            return $this->sendResponse($data, "Company list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_expense_category(Request $request)
    {

        try {
            $data = new ExpenseCategory();
            $data = $data->with(['company']);
            if ($request?->filter_by_status) {
                if ($request?->filter_by_status != "all") {
                    $data = $data->where('status', $request?->filter_by_status);
                }
            } else {
                $data = $data->where('status', 'active');
            }
            $data = $data->orderBy('name', 'asc');
            $data = $data->get();

            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Expense Category list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /** API Generate Employee Code */
    public function generate_employee_code(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ]
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $preFix = "";
            $codeLength = 3;
            $employeeCodeStart = 0;
            $employeeCount = Employee::withTrashed()->where('company_id', $company_id);
            if ($request?->branch_id) {
                $branch = Branch::where('company_id', $company_id)->where('id', $request?->branch_id)->first();
                if ($branch && $branch?->prefix) {
                    $preFix = $branch?->prefix;
                }
                $employeeCount = $employeeCount->where('branch_id', $request?->branch_id);
            }else{
                $branch = Branch::where('company_id', $company_id)->first();
                if ($branch && $branch?->prefix) {
                    $preFix = $branch?->prefix;
                }
                $employeeCodeStart = (int)$branch?->employee_code_start;
                if($branch?->employee_code_start){
                    $codeLength = strlen((string)$branch->employee_code_start);
                }
                // dd($branch->toArray(), $branch->employee_code_start, $codeLength);
            }
            $employeeCount = $employeeCount->count();
            
            // $nextNumber = $employeeCount + 1;
            $employeeCodeStart = $employeeCodeStart + $employeeCount ;
            $employeeCode = $preFix . str_pad($employeeCodeStart, $codeLength, '0', STR_PAD_LEFT);
            // dd($branch->toArray(), $branch->employee_code_start, $codeLength, $employeeCode);


            if ($request->directReturn) {
                return $employeeCode;
            }

            return $this->sendResponse($employeeCode, "Employee code generated.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in generate employee code API");
    }

    /** API Get Company Setting */
    public function get_company_setting(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ]
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company = Company::find($request->company_id);
            
            if (!$company) {
                return $this->sendError("Company not found");
            }

            $data = [
                'employee_code_auto_generation' => $company->employee_code_auto_generation ?? 'auto',
            ];

            return $this->sendResponse($data, "Company setting retrieved successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in get company setting API");
    }



    /** API Get Designation */
    public function get_designation(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request?->company_id;
            $designation = Designation::where('company_id', $company_id)->where('status', 'active');
            $data = $designation->orderBy('name', 'asc')->get();
            $data = $data->map(function ($row) {
                $temp['id'] = $row?->id . "";
                $temp['designation_name'] = $row?->name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Designation list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_team_role(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request?->company_id;
            $team_role = TeamRole::where('company_id', $company_id)->where('status', 'active');
            $data = $team_role->orderBy('name', 'asc')->get();
            $data = $data->map(function ($row) {
                $temp['id'] = $row?->id . "";
                $temp['team_role_name'] = $row?->name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Team Role list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }


    public function get_document_type(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }
            $data = DocumentType::query();
            $data = $data->where('company_id', $request?->company_id);

            if ($request?->filter_by_status) {
                if ($request?->filter_by_status != "all") {
                    $data = $data->where('status', $request?->filter_by_status);
                }
            } else {
                $data = $data->where('status', 'active');
            }

            $data = $data->orderBy('name', 'asc')->get();
            $data = $data->map(function ($row) {
                $temp['id'] = $row?->id . "";
                $temp['document_type_name'] = $row?->name . "";
                $temp['company_id'] = $row?->company_id . "";
                $temp['company_name'] = $row?->company?->company_name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Company wise document list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }


    public function get_subscription_plan(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ],
                'plan_id' => [
                    'nullable',
                    Rule::exists((new PlanMaster())->getTable(), 'id')
                ],
                'subscription_id' => [
                    'required',
                    Rule::exists((new CompanySubscriptionPlan())->getTable(), 'id')
                ]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }
            $company_id = $request?->company_id;
            $plan_id = $request?->plan_id;
            $subscription_id = $request?->subscription_id;

            $query = CompanySubscriptionPlan::with(['plan', 'company'])->select('*')
                ->where('company_id', $company_id)->where('id', $subscription_id)->where('plan_id', $plan_id)->where('subscription_status', 'active');
            $data = $query->orderBy('id', 'desc')->get();
            $data = $data->map(function ($row) {
                $temp['id'] = $row?->id . "";
                $temp['company_name'] = $row?->company?->company_name . "";
                $temp['plan_name'] = $row?->plan?->name . "";
                return $temp;
            });

            return $this->sendResponse($data, "Subscription Plan list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function get_plans(Request $request)
    {
        try {
            $plan_master = PlanMaster::where('status', 'active');

            $isMasterAdmin = Auth::guard('admin_software')->check() || (Auth::guard('employees')->check() && Auth::guard('employees')->user()->company_id == 1);

            $companyId = $request->input('company_id') ?: (Auth::guard('employees')->check() ? Auth::guard('employees')->user()->company_id : null);

            if (!$isMasterAdmin && $companyId) {
                $planIds = CompanySubscriptionPlan::where('company_id', $companyId)->pluck('plan_id')->toArray();
                $compPlan = Company::where('id', $companyId)->value('plan_id');
                if ($compPlan) {
                    $planIds[] = $compPlan;
                }
                $planIds = array_unique(array_filter($planIds));
                if (!empty($planIds)) {
                    $plan_master->whereIn('id', $planIds);
                }
            } elseif ($request->filled('company_id')) {
                $planIds = CompanySubscriptionPlan::where('company_id', $request->company_id)->pluck('plan_id')->toArray();
                $compPlan = Company::where('id', $request->company_id)->value('plan_id');
                if ($compPlan) {
                    $planIds[] = $compPlan;
                }
                $planIds = array_unique(array_filter($planIds));
                if (!empty($planIds)) {
                    $plan_master->whereIn('id', $planIds);
                }
            }

            $plan_master = $plan_master->orderBy('id', 'desc')->get();

            return $this->sendResponse($plan_master, 'Plan list');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
        }
        return $this->sendError("Something want to wrong in Plan list API");
    }


    public function getLocationByPincode(Request $request)
    {
        $pincode = $request->pincode;

        // Validate pincode
        if (!$pincode || strlen($pincode) != 6) {
            return response()->json(['status' => false, 'message' => 'Invalid Pincode']);
        }

        // Call India Post API
        $response = Http::withHeaders([
            'User-Agent' => 'Laravel-HttpClient'
        ])->get("https://api.postalpincode.in/pincode/{$pincode}");
        $data = $response->json();

        // If response contains valid PostOffice data
        if (!empty($data[0]['PostOffice'])) {
            $postOffices = $data[0]['PostOffice'];
            $firstPO = $postOffices[0];

            // Extract Country and State
            $countryName = $firstPO['Country'] ?? 'India';
            $stateName   = $firstPO['State'] ?? null;

            // Match with database
            $country = MasterCountry::where('name', $countryName)->first();
            $state   = MasterState::where('name', $stateName)->first();

            // Collect city names from only 'Name'
            $cityNames = [];
            foreach ($postOffices as $po) {
                if (!empty($po['Name'])) {
                    $cityNames[] = $po['Name'];
                }
            }

            // Remove duplicates
            $cityNames = collect($cityNames)->unique()->values();

            // Get all matched cities
            $cities = MasterCity::whereIn('name', $cityNames)->get();

            // Try selecting a city that exactly matches the first PostOffice name
            $selectedCity = null;
            if (!empty($firstPO['Name'])) {
                $selectedCity = MasterCity::where('name', $firstPO['Name'])->first();
            }

            // Return response
            return response()->json([
                'status' => true,
                'data' => [
                    'country_id'       => $country?->id,
                    'state_id'         => $state?->id,
                    'cities'           => $cities->map(fn($city) => [
                        'id' => $city->id,
                        'name' => $city->name
                    ]),
                    'selected_city_id' => $selectedCity?->id
                ]
            ]);
        }

        // No valid post office data found
        return response()->json(['status' => false, 'message' => 'Pincode not found']);
    }

    // Department
    public function get_department(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $departments = Department::where('company_id', $company_id)
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();

            $data = $departments->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'department_name' => (string) $item->name,
                ];
            });

            return $this->sendResponse($data, "Department list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Department API.");
    }

    public function get_subdepartment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
                'department_id' => [
                    'required',
                    Rule::exists((new Department())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;
            $department_id = $request->department_id;

            $departments = SubDepartment::where('company_id', $company_id)
                ->where('department_id', $department_id) // 👈 added filter
                ->where('status', 'active')
                ->orderBy('sub_department_name', 'asc')
                ->get();

            $data = $departments->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'sub_department_name' => (string) $item->sub_department_name,
                ];
            });

            return $this->sendResponse($data, "Sub Department list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Sub Department API.");
    }
    // public function get_process(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'company_id' => [
    //                 'required',
    //                 Rule::exists((new Company())->getTable(), 'id'),
    //             ],
    //         ]);

    //         if ($validator->fails()) {
    //             return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
    //         }

    //         $company_id = $request->company_id;

    //         $processes =Process::where('company_id', $company_id)
    //             ->where('status', 'active')
    //             ->orderBy('name', 'asc')
    //             ->get();

    //         $data = $processes->map(function ($item) {
    //             return [
    //                 'id' => (string) $item->id,
    //                 'name' => (string) $item->name,
    //             ];
    //         });

    //         return $this->sendResponse($data, "Process list.");
    //     } catch (\Exception $e) {
    //         return $this->sendError($e->getMessage());
    //     }

    //     return $this->sendError("Something went wrong in Process API.");
    // }
    public function get_process(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
                'department_id' => [
                    'nullable',
                    Rule::exists((new Department())->getTable(), 'id'),
                ],
                'sub_department_id' => [
                    'nullable',
                    Rule::exists((new SubDepartment())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;
            $department_id = $request->department_id;
            $sub_department_id = $request->sub_department_id;

            $query = Process::query()
                ->where('company_id', $company_id)
                ->where('status', 'active');

            if (!empty($department_id)) {
                $query->where('department_id', $department_id);
            }

            if (!empty($sub_department_id)) {
                $query->where('sub_department_id', $sub_department_id);
            }

            $processes = $query->orderBy('name', 'asc')->get();

            $data = $processes->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                ];
            });

            return $this->sendResponse($data, "Process list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Process API.");
    }

    public function get_branch(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $branchs = Branch::where('company_id', $company_id)
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();

            $data = $branchs->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                ];
            });

            return $this->sendResponse($data, "Branch list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Branch API.");
    }

    public function get_assets(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $assets = AssetsAllocationMaster::where('company_id', $company_id)
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();

            $data = $assets->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                ];
            });

            return $this->sendResponse($data, "Assets Allocation list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Assets Allocation API.");
    }

    public function get_loan_types(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $assets = LoanTypes::where('company_id', $company_id)
                ->where('status', 'active')
                ->orderBy('display_order', 'asc')
                ->get();

            $data = $assets->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'name' => (string) $item->name,
                    'display_order' => (string) $item->display_order,
                ];
            });

            return $this->sendResponse($data, "Loan type list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something went wrong in Loan type API.");
    }

    public function get_employee(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            // Eager-load latest employment detail so we can expose shift info
            $employeeQuery = Employee::with(['employmentDetail'])->where('company_id', $company_id);
            if ($request?->branch_id) {
                $employeeQuery->where('branch_id', $request?->branch_id);
            }
            $employeeQuery = $employeeQuery->where('status', 'active');

            // Exclude contractor employees when requested (used by Leave Application)
            if ($request->has('exclude_contractor') && $request->exclude_contractor) {
                $contractTypeIds = EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
                if ($contractTypeIds->isNotEmpty()) {
                    $employeeQuery->whereDoesntHave('employmentDetail', function ($q) use ($contractTypeIds) {
                        $q->whereIn('employment_type', $contractTypeIds);
                    });
                }
            }

            // Include ONLY contractor employees when requested (used by Contractor Leave Application, etc.)
            if (($request->has('employee_type') && $request->employee_type === 'contractor') || ($request->has('only_contractor') && $request->only_contractor)) {
                $contractTypeIds = EmployeeType::where('name', 'like', '%contract%')->orWhere('name', 'like', '%contractor%')->pluck('id');
                if ($contractTypeIds->isNotEmpty()) {
                    $employeeQuery->whereHas('employmentDetail', function ($q) use ($contractTypeIds) {
                        $q->whereIn('employment_type', $contractTypeIds);
                    });
                } else {
                    $employeeQuery->whereRaw('1 = 0');
                }
            }
            
            $employeeQuery = $employeeQuery->orderBy('employee_code', 'asc');
            
            $employees = $employeeQuery->get();


            // Preload all shifts referenced in employment details to avoid N+1 queries
            $shiftIds = $employees->map(function ($emp) {
                return optional($emp->employmentDetail)->shift;
            })->filter()->unique()->values();

            $shifts = $shiftIds->isNotEmpty()
                ? Shift::whereIn('id', $shiftIds)->pluck('name', 'id')
                : collect();

            $data = $employees->map(function ($item) use ($shifts) {
                $shiftId = optional($item->employmentDetail)->shift;
                $shiftName = $shiftId && isset($shifts[$shiftId]) ? $shifts[$shiftId] : null;

                return [
                    'id' => (string) $item->id,
                    'company_id' => (string) $item->company_id,
                    'branch_id' => (string) $item->branch_id,
                    'employee_code' => (string) $item->employee_code,
                    'first_name' => (string) $item->first_name ?? '-',
                    'middle_name' => (string) $item->middle_name ?? '-',
                    'father_name' => (string) $item->father_name ?? '-',
                    'full_name' => (string) $item->proper_name ?? '-',
                    'gender' => (string) $item->gender ?? '-',
                    'status' => (string) $item->status ?? '-',
                    'shift_id' => $shiftId ? (string) $shiftId : '',
                    'shift_name' => $shiftName ? (string) $shiftName : '',
                ];
            });

            return $this->sendResponse($data, "Employee list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Employee API.");
    }
    public function get_employedetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists('companies', 'id')],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $employeeQuery = Employee::query()
                ->where('employees.company_id', $request->company_id)
                ->where('employees.status', 'active');

            if ($request->department_id) {
                $employeeQuery->join('employment_details', 'employees.id', '=', 'employment_details.employee_id')
                    ->where('employment_details.department_id', $request->department_id);
            }

            $employees = $employeeQuery->orderBy('employees.employee_code', 'asc')
                ->select('employees.id', 'employees.company_id', 'employees.employee_code', 'employees.full_name', 'employees.first_name', 'employees.middle_name', 'employees.father_name')
                ->get();

            $employees = $employees->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'company_id' => (string) $item->company_id,
                    'employee_code' => (string) $item->employee_code,
                    'full_name' => (string) $item->proper_name,
                ];
            });

            return $this->sendResponse($employees, "Employee list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }



    // public function get_shift(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'company_id' => [
    //                 'required',
    //                 Rule::exists((new Company())->getTable(), 'id'),
    //             ],
    //         ]);

    //         if ($validator->fails()) {
    //             return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
    //         }

    //         $company_id = $request->company_id;

    //         $shift = Shift::where('company_id', $company_id)
    //             ->where('status', 'active')
    //             ->orderBy('name', 'asc')
    //             ->get();

    //         $data = $shift->map(function ($item) {
    //             return [
    //                 'id' => (string) $item->id,
    //                 'name' => (string) $item->name,
    //             ];
    //         });

    //         return $this->sendResponse($data, "Shift list.");
    //     } catch (\Exception $e) {
    //         return $this->sendError($e->getMessage());
    //     }

    //     return $this->sendError("Something went wrong in Shift API.");
    // }

    public function get_employee_type(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $employee_type = EmployeeType::where('company_id', $company_id);

            $employee_type = $employee_type->where('status', 'active')->orderBy('name', 'asc')->get();

            $data = $employee_type->map(function ($item) {
                // return $item;

                return [
                    'id' => (string) $item->id,
                    'company_id' => (string) $item->company_id,
                    'name' => (string) $item->name,
                    'status' => (string) $item->status ?? '-',
                ];
            });

            return $this->sendResponse($data, "Employee Allocation list.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Employee Allocation API.");
    }

    public function get_shift(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $shift = Shift::where('company_id', $company_id);

            $shift = $shift->where('status', 'active')->orderBy('name', 'asc')->get();

            $data = $shift->map(function ($item) {
                return [
                    'id' => (string) $item->id,
                    'company_id' => (string) $item->company_id,
                    'name' => (string) $item->name,
                    'punch_in_minimum' => (string) ($item->punch_in_minimum ?? ''),
                    'punch_out' => (string) ($item->punch_out ?? ''),
                    'in_out_grace_period' => (int) ($item->in_out_grace_period ?? $item->grace_period ?? 0),
                    'grace_period' => (int) ($item->grace_period ?? 0),
                    'working_hour' => (string) ($item->working_hour ?? ''),
                    'half_day_hour' => (string) ($item->half_day_hour ?? ''),
                    'present_day_hour' => (string) ($item->present_day_hour ?? ''),
                    'status' => (string) ($item->status ?? '-'),
                ];
            });

            return $this->sendResponse($data, "Shift List.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Shift API.");
    }

    public function loan_calculation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'nullable',
                    Rule::exists((new Company())->getTable(), 'id'),
                ],
                'loan_amount' => 'required|numeric|min:1',
                'interest_rate' => 'nullable|numeric|min:0',   // ✅ can be null or 0
                'total_installments' => 'required|integer|min:1',
                'interest_type' => 'nullable|in:flat,reducing', // ✅ defaults to flat
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $company_id = $request->company_id;

            $schedule = Loan::calculateSchedule(
                $request->loan_amount,
                $request->interest_rate,
                $request->total_installments,
                $request->interest_type
            );

            return $this->sendResponse($schedule, "Loan Calculation.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendError("Something went wrong in Shift API.");
    }

    /**
     * Calculate salary for employee - API endpoint
     */
    public function salary_calculation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'branch_id' => ['nullable', Rule::exists((new Branch())->getTable(), 'id')],
                'department_id' => ['nullable', Rule::exists((new Department())->getTable(), 'id')],
                'employee_id' => ['required', Rule::exists((new Employee())->getTable(), 'id')],
                'year' => ['required', 'integer', 'min:2020', 'max:2050'],
                'month' => ['required', 'integer', 'min:1', 'max:12'],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $companyId = $request->company_id;
            $branchId = $request->branch_id;
            $employeeId = $request->employee_id;
            $year = (int) $request->year;
            $month = (int) $request->month;

            // Get employee details
            $employee = Employee::with(['branch'])->find($employeeId);
            if (!$employee) {
                return $this->sendError('Employee not found', [], [], 404);
            }

            // Get salary details for employee
            $salaryDetail = EmployeeWiseSalaryDetail::where('company_id', $companyId)
                ->where('employee_id', $employeeId)
                ->first();

            if (!$salaryDetail) {
                return $this->sendError('Salary details not found for this employee. Please configure salary details first.', [], [], 404);
            }

            // Calculate date range for the month
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $totalDaysInMonth = $startDate->daysInMonth;

            // Get attendance data
            $attendanceData = $this->getSalaryAttendanceData($companyId, $employeeId, $startDate, $endDate, $salaryDetail);

            // Get holiday count
            $holidayCount = $this->getSalaryHolidayCount($companyId, $startDate, $endDate);

            // Get leave data
            $leaveData = $this->getSalaryLeaveData($companyId, $employeeId, $startDate, $endDate);

            // Get bonus data
            $bonusData = $this->getSalaryBonusData($companyId, $employeeId, $branchId, $year, $month);

            // Get loan EMI data
            $loanData = $this->getSalaryLoanData($companyId, $employeeId);

            // Calculate per day salary
            $ctc = $salaryDetail->ctc ?? 0;
            $perDaySalary = $ctc / 30; // Based on 30 days calculation

            // Calculate salary components
            $calculatedData = $this->calculateSalaryComponents(
                $salaryDetail,
                $attendanceData,
                $holidayCount,
                $leaveData,
                $bonusData,
                $loanData,
                $totalDaysInMonth,
                $perDaySalary
            );

            return $this->sendResponse($calculatedData, 'Salary calculated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error calculating salary: ' . $e->getMessage());
        }
    }

    /**
     * Get attendance data for salary calculation
     */
    private function getSalaryAttendanceData($companyId, $employeeId, $startDate, $endDate, $salaryDetail)
    {
        $today = Carbon::today();
        $attendances = Attendance::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $attendanceByDate = $attendances->groupBy('attendance_date');
        $presentDays = 0;
        $halfDays = 0;
        $absentDays = 0;
        $totalWorkingMinutes = 0;
        $totalOtHours = 0;
        $otDays = 0;

        // Get week off days
        $weekOffDays = [];
        if ($salaryDetail && $salaryDetail->week_off) {
            $weekOffArray = json_decode($salaryDetail->week_off, true);
            $dayMap = [
                'sun' => 0, 'sunday' => 0, 'mon' => 1, 'monday' => 1,
                'tue' => 2, 'tuesday' => 2, 'wed' => 3, 'wednesday' => 3,
                'thu' => 4, 'thursday' => 4, 'fri' => 5, 'friday' => 5,
                'sat' => 6, 'saturday' => 6
            ];
            if (is_array($weekOffArray)) {
                foreach ($weekOffArray as $wo) {
                    $woStr = strtolower(trim($wo));
                    if (isset($dayMap[$woStr])) {
                        $weekOffDays[] = $dayMap[$woStr];
                    }
                }
            }
        }

        $weekOffCount = 0;

        for ($day = $startDate->copy(); $day->lte($endDate); $day->addDay()) {
            $dateStr = $day->format('Y-m-d');
            $dayRecords = $attendanceByDate->get($dateStr, collect());

            if (in_array($day->dayOfWeek, $weekOffDays)) {
                $weekOffCount++;
                continue;
            }

            if ($dayRecords->count() > 0) {
                $allPunches = $dayRecords->map(fn($r) => [
                    'type' => $r->attendace_type,
                    'time' => $r->punch_in_time
                ])->values()->toArray();

                $dailyMinutes = 0;
                for ($i = 0; $i < count($allPunches); $i++) {
                    if (
                        strtolower($allPunches[$i]['type']) === 'in' &&
                        isset($allPunches[$i + 1]) &&
                        strtolower($allPunches[$i + 1]['type']) === 'out'
                    ) {
                        $inTime = Carbon::parse($allPunches[$i]['time']);
                        $outTime = Carbon::parse($allPunches[$i + 1]['time']);
                        $dailyMinutes += $outTime->diffInMinutes($inTime);
                        $i++;
                    }
                }

                $totalWorkingMinutes += $dailyMinutes;

                if ($dailyMinutes > 0 && $dailyMinutes < 240) {
                    $halfDays++;
                } else if ($dailyMinutes >= 240) {
                    $presentDays++;
                    if ($dailyMinutes > 480 && $salaryDetail->overtime == 'yes') {
                        $otMinutes = $dailyMinutes - 480;
                        $totalOtHours += ($otMinutes / 60);
                        $otDays++;
                    }
                }
            } else {
                if ($day->lte($today)) {
                    $absentDays++;
                }
            }
        }

        $totalHours = floor($totalWorkingMinutes / 60);
        $totalMins = $totalWorkingMinutes % 60;
        $workingHoursFormatted = sprintf('%02d:%02d:00', $totalHours, $totalMins);

        return [
            'total_present_day' => $presentDays,
            'half_day' => $halfDays,
            'total_absent' => $absentDays,
            'total_week_off' => $weekOffCount,
            'working_hour' => $workingHoursFormatted,
            'actual_total_ot_hours' => round($totalOtHours, 2),
            'earn_ot_hours' => round($totalOtHours, 2),
            'earn_ot_days' => $otDays,
        ];
    }

    /**
     * Get holiday count for salary calculation
     */
    private function getSalaryHolidayCount($companyId, $startDate, $endDate)
    {
        $holidays = Holiday::where('company_id', $companyId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('from_date', '<', $startDate)->where('to_date', '>', $endDate));
            })->get();

        $holidayCount = 0;
        foreach ($holidays as $h) {
            $from = Carbon::parse($h->getRawOriginal('from_date'))->startOfDay();
            $to = Carbon::parse($h->getRawOriginal('to_date'))->startOfDay();
            if ($from->lt($startDate)) $from = $startDate->copy();
            if ($to->gt($endDate)) $to = $endDate->copy();
            $holidayCount += $from->diffInDays($to) + 1;
        }

        return $holidayCount;
    }

    /**
     * Get leave data for salary calculation
     */
    private function getSalaryLeaveData($companyId, $employeeId, $startDate, $endDate)
    {
        $leaves = LeaveApplication::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('fromdate_time', [$startDate, $endDate])
                    ->orWhereBetween('todate_time', [$startDate, $endDate])
                    ->orWhere(fn($q2) => $q2->where('fromdate_time', '<', $startDate)->where('todate_time', '>', $endDate));
            })
            ->with('leave_type')
            ->get();

        $totalLeave = 0;
        $companyPayLeave = 0;
        $employeePayLeave = 0;
        $sandwichLeave = 0;

        foreach ($leaves as $leave) {
            $from = Carbon::parse($leave->fromdate_time)->startOfDay();
            $to = Carbon::parse($leave->todate_time ?? $leave->fromdate_time)->startOfDay();
            if ($from->lt($startDate)) $from = $startDate->copy();
            if ($to->gt($endDate)) $to = $endDate->copy();

            $leaveDays = $from->diffInDays($to) + 1;
            if ($leave->halfday_fullday === 'halfday') {
                $leaveDays = 0.5;
            }

            $totalLeave += $leaveDays;
            if ($leave->leave_type && $leave->leave_type->mode == 1) {
                $companyPayLeave += $leaveDays;
            } else {
                $employeePayLeave += $leaveDays;
            }
        }

        return [
            'total_leave' => $totalLeave,
            'total_company_pay_leave' => $companyPayLeave,
            'total_employee_pay_leave' => $employeePayLeave,
            'total_sandwich_leave' => $sandwichLeave,
        ];
    }

    /**
     * Get bonus data for salary calculation
     */
    private function getSalaryBonusData($companyId, $employeeId, $branchId, $year, $month)
    {
        $bonus = Bonus::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->where(function ($q) use ($employeeId, $branchId) {
                $q->where('employee', $employeeId)
                    ->orWhere('branch', $branchId);
            })
            ->first();

        return [
            'bonus_amount' => $bonus->amount ?? 0,
        ];
    }

    /**
     * Get loan data for salary calculation
     */
    private function getSalaryLoanData($companyId, $employeeId)
    {
        $loan = Loan::where('company_id', $companyId)
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->first();

        return [
            'ded_loan_amount' => $loan->emi_amount ?? 0,
            'loan_balance' => $loan->balance_amount ?? 0,
        ];
    }

    /**
     * Calculate salary components
     */
    private function calculateSalaryComponents($salaryDetail, $attendanceData, $holidayCount, $leaveData, $bonusData, $loanData, $totalDaysInMonth, $perDaySalary)
    {
        $ctc = $salaryDetail->ctc ?? 0;
        $basicDa = $salaryDetail->basic_da ?? 0;
        $hra = $salaryDetail->hra ?? 0;
        $conveyanceAllowance = $salaryDetail->conveyance_allowance ?? 0;
        $medicalAllowance = $salaryDetail->medical_allowance ?? 0;
        $specialAllowance = $salaryDetail->special_allowance ?? 0;

        $calculateDays = $attendanceData['total_present_day'] + ($attendanceData['half_day'] * 0.5) +
            $attendanceData['total_week_off'] + $holidayCount + $leaveData['total_company_pay_leave'];

        $presentDayAmount = $attendanceData['total_present_day'] * $perDaySalary;
        $employeeWeekoffAmount = $attendanceData['total_week_off'] * $perDaySalary;
        $companyPayLeaveAmount = $leaveData['total_company_pay_leave'] * $perDaySalary;
        $employeePayLeaveAmount = $leaveData['total_employee_pay_leave'] * $perDaySalary;

        $fxsBasic = $basicDa;
        $fxsHra = $hra;
        $fxsOther = $conveyanceAllowance + $medicalAllowance + $specialAllowance;
        $fxsTotalEarning = $fxsBasic + $fxsHra + $fxsOther;

        $dwsBasic = ($basicDa / 30) * $calculateDays;
        $dwsDa = 0;
        $dwsOther = (($conveyanceAllowance + $medicalAllowance + $specialAllowance) / 30) * $calculateDays;
        $dwsTotalEarning = $dwsBasic + $dwsDa + $dwsOther;

        $perHourSalary = $perDaySalary / 8;
        $earnOtPayableAmt = $attendanceData['earn_ot_hours'] * $perHourSalary;

        $earnSubTotal = $earnOtPayableAmt + $bonusData['bonus_amount'];
        $totalEarning = $dwsTotalEarning + $earnSubTotal;

        $pfBase = $basicDa;
        $dedEmployeePf = 0;
        $dedPradhanMantriPf = 0;

        if ($salaryDetail->pf == 'yes') {
            $pfPercentage = $salaryDetail->pf_percentage ?? 12;
            $dedEmployeePf = ($pfBase * $pfPercentage) / 100;
        }

        if ($salaryDetail->pradhanmantri_pf == 'yes') {
            $pmPfPercentage = $salaryDetail->pradhanmantri_pf_percentage ?? 0;
            $dedPradhanMantriPf = ($pfBase * $pmPfPercentage) / 100;
        }

        $dedEsiEmployee = 0;
        $dedEsiCompany = 0;

        if ($salaryDetail->esi_employee_side == 'yes') {
            $esiEmployeePercentage = $salaryDetail->esi_employee_side_percentage ?? 0.75;
            $dedEsiEmployee = ($ctc * $esiEmployeePercentage) / 100;
        }

        if ($salaryDetail->is_esi_company_side == 'yes') {
            $esiCompanyPercentage = $salaryDetail->esi_company_side_percentage ?? 3.25;
            $dedEsiCompany = ($ctc * $esiCompanyPercentage) / 100;
        }

        $dedPt = 0;
        if ($salaryDetail->pt == 'yes') {
            $dedPt = $salaryDetail->pt_amount ?? 0;
        }

        $dedInsurance = 0;
        if ($salaryDetail->insurance == 'yes') {
            $dedInsurance = $salaryDetail->insurance_amount ?? 0;
        }

        $dedTds = 0;
        if ($salaryDetail->tds == 'yes') {
            $tdsPercentage = $salaryDetail->tds_percentage ?? 0;
            $dedTds = ($ctc * $tdsPercentage) / 100;
        }

        $dedWf = 0;
        if ($salaryDetail->is_welfare_fund_applied == 'yes') {
            $dedWf = 10;
        }

        $totalDeduction = $dedEmployeePf + $dedPradhanMantriPf + $dedEsiEmployee +
            $dedPt + $dedInsurance + $dedTds + $dedWf + $loanData['ded_loan_amount'];

        $netBankPay = $totalEarning - $totalDeduction;

        return [
            'calculate_days' => round($calculateDays, 2),
            'total_present_day' => $attendanceData['total_present_day'],
            'half_day' => $attendanceData['half_day'],
            'holiday' => $holidayCount,
            'total_week_off' => $attendanceData['total_week_off'],
            'total_sandwich_leave' => $leaveData['total_sandwich_leave'],
            'total_leave' => $leaveData['total_leave'],
            'total_company_pay_leave' => $leaveData['total_company_pay_leave'],
            'total_employee_pay_leave' => $leaveData['total_employee_pay_leave'],
            'total_absent' => $attendanceData['total_absent'],
            'total_day' => $totalDaysInMonth,
            'working_hour' => $attendanceData['working_hour'],
            'ctc' => round($ctc, 2),
            'per_day_salary' => round($perDaySalary, 2),
            'conveyance_allowance' => round($conveyanceAllowance, 2),
            'medical_allowance' => round($medicalAllowance, 2),
            'special_allowance' => round($specialAllowance, 2),
            'present_day_amount' => round($presentDayAmount, 2),
            'employee_weekoff_amount' => round($employeeWeekoffAmount, 2),
            'company_pay_leave_amount' => round($companyPayLeaveAmount, 2),
            'employee_pay_leave_amount' => round($employeePayLeaveAmount, 2),
            'fxs_basic' => round($fxsBasic, 2),
            'fxs_hra' => round($fxsHra, 2),
            'fxs_other' => round($fxsOther, 2),
            'fxs_total_earning' => round($fxsTotalEarning, 2),
            'dws_basic' => round($dwsBasic, 2),
            'dws_da' => round($dwsDa, 2),
            'dws_other' => round($dwsOther, 2),
            'dws_total_earning' => round($dwsTotalEarning, 2),
            'actual_total_ot_hours' => round($attendanceData['actual_total_ot_hours'], 2),
            'earn_ot_hours' => round($attendanceData['earn_ot_hours'], 2),
            'earn_ot_payable_amt' => round($earnOtPayableAmt, 2),
            'earn_ot_days' => $attendanceData['earn_ot_days'],
            'bonus_amount' => round($bonusData['bonus_amount'], 2),
            'bonus_amount_adjustment' => 0,
            'earn_sub_total' => round($earnSubTotal, 2),
            'total_earning' => round($totalEarning, 2),
            'ded_employee_pf' => round($dedEmployeePf, 2),
            'ded_pradhan_mantri_pf' => round($dedPradhanMantriPf, 2),
            'ded_esi_employee' => round($dedEsiEmployee, 2),
            'ded_esi_company' => round($dedEsiCompany, 2),
            'ded_pt' => round($dedPt, 2),
            'ded_insurance' => round($dedInsurance, 2),
            'ded_tds' => round($dedTds, 2),
            'tds_amount_adjustment' => 0,
            'ded_wf' => round($dedWf, 2),
            'ded_loan_amount' => round($loanData['ded_loan_amount'], 2),
            'loan_amount_adjustment' => 0,
            'ded_other' => 0,
            'total_deduction' => round($totalDeduction, 2),
            'net_bank_pay' => round($netBankPay, 2),

            // PF/ESI rules for client-side/re-calculations
            'pf_enabled' => in_array($salaryDetail->pf, ['yes', '1', 1, true], true),
            'pf_percentage' => (float) ($salaryDetail->pf_percentage ?? 12),
            'pradhanmantri_pf_enabled' => in_array($salaryDetail->pradhanmantri_pf, ['yes', '1', 1, true], true),
            'pradhanmantri_pf_percentage' => (float) ($salaryDetail->pradhanmantri_pf_percentage ?? 0),
            'esi_employee_enabled' => in_array($salaryDetail->esi_employee_side, ['yes', '1', 1, true], true),
            'esi_employee_percentage' => (float) ($salaryDetail->esi_employee_side_percentage ?? 0.75),
            'esi_company_enabled' => in_array($salaryDetail->is_esi_company_side, ['yes', '1', 1, true], true),
            'esi_company_percentage' => (float) ($salaryDetail->esi_company_side_percentage ?? 3.25),
        ];
    }
}