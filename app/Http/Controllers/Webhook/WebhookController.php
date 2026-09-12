<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\BiometricMachine;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct() {}
    

    public function getCompanyDetail(Request $request)
    {
        try {

            $validator =  Validator::make($request->all(), [
                'app_key' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'app_key')
                ]
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }


            /** GEt the  company detaill */
            $data = new Company();
            $data = $data->select(['id', 'company_name', 'person_name', 'app_key', 'plan_from', 'plan_to', 'status']);
            $data = $data->where('app_key', $request?->app_key);
            $data = $data->orderBy('id', 'DESC');
            $data = $data->first();
            // , 'webhook_url_endpoint'
            if ($data) {
                $data['webhook_url_endpoint'] = env("APP_URL") . 'webhook/' . $request?->app_key;
                $data['company_id'] = $data?->id . "";
                $data['set_timeout'] = "30";
            }

            if($request?->directReturn){
                return $data;
            }
            return $this->sendResponse($data, "Successfully get company detail.");
            return $request->all();
        } catch (\Exception $e) {
            if($request?->directReturn){
                dd("L-52", $e->getMessage());
                return $e->getMessage();
            }
            return $this->sendError($e->getMessage(), [], [], 404);
        } catch (\Throwable $th) {
            if($request?->directReturn){
                dd("L-57",$th->getMessage());
            }
            //throw $th;
        }

        return $this->sendError("Something want to wrong.");
    }

    public function verifyStaticUser(Request $request)
    {
        try {

            $validator =  Validator::make($request->all(), [
                'username' => [
                    'required',
                    'string',
                ],
                'password' => [
                    'required',
                    'string',
                ],
                'app_key' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'app_key')
                ]
            ]);

            $validator->after(function ($validator) use ($request) {
                if ($request->username !== 'nimit.ocean' || $request->password !== 'ocean@2025') {
                    $validator->errors()->add('credentials', 'Invalid username or password');
                }
            });

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $request['directReturn'] = true;
            $companyDetail = self::getCompanyDetail($request);

            /** GEt the  company detaill */
            if($companyDetail && $companyDetail?->id){
                $data = $companyDetail;
            }else{
                $data = new Company();
                $data = $data->select(['id', 'company_name', 'person_name', 'app_key', 'plan_from', 'plan_to', 'status']);
                $data = $data->where('app_key', $request?->app_key);
                $data = $data->orderBy('id', 'DESC');
                $data = $data->first();
                // , 'webhook_url_endpoint'
                if ($data) {
                    $data['webhook_url_endpoint'] = env("APP_URL") . 'webhook/' . $request?->app_key;
                    $data['company_id'] = $data?->id . "";
                }
            }

            return $this->sendResponse($data, "Verify Successfully.");
            return $request->all();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 404);
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $this->sendError("Something want to wrong.");
    }

    public function formWiseFields(Request $request)
    {
        try {

            $validator =  Validator::make($request->all(), [
                'form_type' => [
                    'required',
                    'string',
                    'in:attendance'
                ],
            ]);


            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
            }

            $removeFields = ["remark", "status", "created_by", "updated_by", "created_at", "updated_at", "deleted_by", "deleted_at"];

            $data = [];
            $returnMessage = "return message";
            if ($request?->form_type == "attendance") {
                $data = (new Attendance())->getFillable();
                $returnMessage = "Attendance From Fields";
            }

            // Remove unwanted fields if they exist
            $data = array_values(array_diff($data, $removeFields));

            return $this->sendResponse($data, $returnMessage);
            return $request->all();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 404);
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $this->sendError("Something want to wrong.");
    }

    /**
     * Receive webhook payload
     */
    public function receive(Request $request)
    {
        try {
            // Handle incoming webhook payload here
            \Illuminate\Support\Facades\Log::info('Webhook received', [
                'headers' => $request->headers->all(),
                'data' => $request->all(),
                'raw_content' => $request->getContent(),
            ]);
            
            return $this->sendResponse($request->all(), 'Webhook received successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], [], 500);
        }
    }

    /**
     * Test webhook endpoint
     */
    public function test(Request $request)
    {
        return $this->sendResponse(null, 'Webhook test route working.');
    }

    /**
     * Biometric Machine Add / Edit via Webhook API
     * 
     * This endpoint allows you to create or update biometric machine records.
     * Use the `edit_id` parameter to determine the operation:
     * - **Create**: Omit `edit_id` parameter
     * - **Update**: Include `edit_id` with the record ID
     * 
     * @group Biometric Machines
     * 
     * @bodyParam edit_id integer optional The ID of the biometric machine to update. Required for update operations. Example: 1
     * @bodyParam ip_address string required The IP address of the biometric machine. Must be a valid IP address. Example: 192.168.1.100
     * @bodyParam port string required The port number (1-65535). Example: 8080
     * @bodyParam machine_name string optional The name of the biometric machine. Example: Main Entrance Device
     * @bodyParam description string optional Description of the biometric machine. Example: Front door biometric scanner
     * @bodyParam status string optional Status of the machine. Must be 'active' or 'inactive'. Default: active. Example: active
     * 
     * @response 200 {
     *   "status": true,
     *   "message": "Biometric machine created successfully.",
     *   "data": {
     *     "id": 1,
     *     "company_id": "1",
     *     "ip_address": "192.168.1.100",
     *     "port": "8080",
     *     "machine_name": "Main Entrance Device",
     *     "description": "Front door biometric scanner",
     *     "status": "active",
     *     "created_at": "2025-11-15T12:00:00.000000Z",
     *     "updated_at": "2025-11-15T12:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "status": false,
     *   "message": "The ip address has already been taken.",
     *   "messages": {
     *     "ip_address": ["The ip address has already been taken."]
     *   }
     * }
     * @response 401 {
     *   "status": false,
     *   "message": "Company not verified."
     * }
     */
    public function biometricMachineAddEdit(Request $request)
    {
        try {
            // Get verified company from middleware
            $company = $request->input('verified_company');
            
            if (!$company) {
                return $this->sendError('Company not verified.', [], [], 401);
            }

            // Get edit_id to determine if it's create or update
            $editId = $request->input('edit_id');
            $isUpdate = !empty($editId);

            // Validation rules
            $rules = [
                'ip_address' => [
                    'required',
                    'ip',
                ],
                'port' => [
                    'required',
                    'numeric',
                    'min:1',
                    'max:65535',
                ],
                'machine_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'description' => [
                    'nullable',
                    'string',
                ],
                'status' => [
                    'nullable',
                    'in:active,inactive',
                ],
            ];

            // Add unique validation for IP:Port combination (ignore current record if updating)
            if ($isUpdate) {
                $rules['ip_address'][] = Rule::unique((new BiometricMachine())->getTable())->where(function ($query) use ($company, $request) {
                    return $query->where('company_id', $company->id)
                                 ->where('port', $request->port);
                })->ignore($editId);
            } else {
                $rules['ip_address'][] = Rule::unique((new BiometricMachine())->getTable())->where(function ($query) use ($company, $request) {
                    return $query->where('company_id', $company->id)
                                 ->where('port', $request->port);
                });
            }

            // If updating, validate edit_id exists
            if ($isUpdate) {
                $rules['edit_id'] = [
                    'required',
                    'numeric',
                    Rule::exists((new BiometricMachine())->getTable(), 'id')->where('company_id', $company->id),
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            // Prepare data
            $data = [
                'company_id' => $company->id,
                'ip_address' => $request->ip_address,
                'port' => $request->port,
                'machine_name' => $request->machine_name,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
            ];

            if ($isUpdate) {
                // Update existing record
                $biometricMachine = BiometricMachine::where('id', $editId)
                    ->where('company_id', $company->id)
                    ->firstOrFail();

                $biometricMachine->update($data);

                Log::info('Biometric machine updated via webhook', [
                    'company_id' => $company->id,
                    'edit_id' => $editId,
                    'ip_address' => $request->ip_address,
                    'port' => $request->port,
                    'machine_id' => $biometricMachine->id,
                ]);

                return $this->sendResponse($biometricMachine, 'Biometric machine updated successfully.');
            } else {
                // Create new record
                $biometricMachine = BiometricMachine::create($data);

                Log::info('Biometric machine created via webhook', [
                    'company_id' => $company->id,
                    'ip_address' => $request->ip_address,
                    'port' => $request->port,
                    'machine_id' => $biometricMachine->id,
                ]);

                return $this->sendResponse($biometricMachine, 'Biometric machine created successfully.');
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Biometric machine not found.', [], [], 404);
        } catch (\Exception $e) {
            Log::error('Error in biometric machine ADD EDIT via webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'edit_id' => $request->input('edit_id'),
            ]);
            return $this->sendError($e->getMessage(), [], [], 500);
        }
    }

    /**
     * List Biometric Machines via Webhook API
     * 
     * This endpoint retrieves a list of biometric machines for the authenticated company.
     * You can filter by status (active/inactive) and search by machine name, IP address, or port.
     * 
     * @group Biometric Machines
     * 
     * @bodyParam status string optional Filter by status. Must be 'active' or 'inactive'. Example: active
     * @bodyParam search string optional Search by machine name, IP address, or port. Example: 192.168
     * @bodyParam page integer optional Page number for pagination. Default: 1. Example: 1
     * @bodyParam per_page integer optional Number of records per page. Default: 15. Example: 15
     * 
     * @response 200 {
     *   "status": true,
     *   "message": "Biometric machines retrieved successfully.",
     *   "data": {
     *     "total": "10",
     *     "perPage": 15,
     *     "currentPage": 1,
     *     "totalPage": "1",
     *     "items": [
     *       {
     *         "id": "1",
     *         "company_id": "1",
     *         "machine_name": "Main Entrance Device",
     *         "ip_address": "192.168.1.100",
     *         "port": "8080",
     *         "description": "Front door biometric scanner",
     *         "status": "active",
     *         "created_at": "2025-11-15T12:00:00.000000Z",
     *         "updated_at": "2025-11-15T12:00:00.000000Z"
     *       },
     *       {
     *         "id": "2",
     *         "company_id": "1",
     *         "machine_name": "Back Entrance Device",
     *         "ip_address": "192.168.1.101",
     *         "port": "8081",
     *         "description": "Back door biometric scanner",
     *         "status": "active",
     *         "created_at": "2025-11-15T12:00:00.000000Z",
     *         "updated_at": "2025-11-15T12:00:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     * @response 401 {
     *   "status": false,
     *   "message": "Company not verified."
     * }
     */
    public function biometricMachineList(Request $request)
    {
        try {
            // Get verified company from middleware
            $company = $request->input('verified_company');
            
            if (!$company) {
                return $this->sendError('Company not verified.', [], [], 401);
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'status' => [
                    'nullable',
                    'in:active,inactive',
                ],
                'search' => [
                    'nullable',
                    'string',
                ],
                'page' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'per_page' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100',
                ],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            /* Pagination */
            $pagination = (isset($request->page)) ? true : false;
            $perPage = (isset($request->per_page)) ? $request->per_page : (env("API_PER_PAGE") ?: 15);
            $page = (isset($request->page)) ? $request->page : 1;

            // Build query
            $moduleDocuments = BiometricMachine::where('company_id', $company->id);

            // Filter by status
            if ($request->filled('status')) {
                $moduleDocuments = $moduleDocuments->where('status', $request->status);
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $moduleDocuments = $moduleDocuments->where(function ($q) use ($search) {
                    $q->where('machine_name', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhere('port', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Order by latest first
            $moduleDocuments = $moduleDocuments->orderBy('id', 'DESC');

            $tempResponseData = [];
            if ($pagination) {
                $moduleDocuments = $moduleDocuments->paginate($perPage);

                $tempResponseData['total'] = "" . $moduleDocuments->total();
                $tempResponseData['perPage'] = $perPage;
                $tempResponseData['currentPage'] = $page;
                $tempResponseData['totalPage'] = "" . $moduleDocuments->lastPage();
                $tempResponseData['items'] = (object)$moduleDocuments->items();
                $tempResponseData['items'] = collect($tempResponseData['items']);
            } else {
                $moduleDocuments = $moduleDocuments->get();
                $tempResponseData['total'] = $moduleDocuments->count() . "";
                $tempResponseData['items'] = $moduleDocuments;
            }

            if (isset($tempResponseData['items'])) {
                $tempResponseData['items'] = $tempResponseData['items']->map(function ($record) {
                    $temp = [];
                    $temp['id'] = $record?->id . "";
                    $temp['company_id'] = $record?->company_id . "";
                    $temp['machine_name'] = $record?->machine_name . "";
                    $temp['ip_address'] = $record?->ip_address . "";
                    $temp['port'] = $record?->port . "";
                    $temp['description'] = $record?->description . "";
                    $temp['status'] = $record?->status . "";
                    $temp['created_at'] = $record?->created_at . "";
                    $temp['updated_at'] = $record?->updated_at . "";
                    return $temp;
                });

                Log::info('Biometric machines listed via webhook', [
                    'company_id' => $company->id,
                    'total' => $tempResponseData['total'],
                    'filters' => [
                        'status' => $request->status,
                        'search' => $request->search,
                    ],
                ]);

                return $this->sendResponse($tempResponseData, 'Biometric machines retrieved successfully.');
            }
            return $this->sendError("Something want to wrong in data fetching.");
        } catch (\Exception $e) {
            Log::error('Error listing biometric machines via webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->sendError($e->getMessage(), [], [], 500);
        }
    }
}
