<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function __construct(Request $request)
    {
    }

    /**
     * Expense Category List
     * company_id is Required
     */
    public function expense_category_list(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $data = new ExpenseCategory();
            $data = $data->with(['company', 'branch']);
            $data = $data->where('company_id', $request?->company_id);

            if ($request?->branch_id) {
                $data = $data->where('branch_id', $request?->branch_id);
            }
            /*
            else {
                $data = $data->whereNull('branch_id');
            }
                */

            if ($request?->filter_by_status) {
                if ($request?->filter_by_status != "all") {
                    $data = $data->where('status', $request?->filter_by_status);
                }
            } else {
                $data = $data->where('status', 'active');
            }

            $data = $data->orderBy('name', 'ASC');
            // $data = $data->get();

            $data = $data->cursor()->map(function ($row) {
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->name . "";
                $temp['company_id'] = $row?->company_id . "";
                $temp['company_name'] = $row?->company?->company_name . "";
                return $temp;
            })->values();
            return $this->sendResponse($data, "Expense Category List.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }


    /**
     * Expense Sub Category List
     * company_id is Required
     * expense_category_id is Required
     * team_person_id when fetch the sub category belongs to team person
     * */
    public function expense_sub_category_list(Request $request)
    {
        // return $request->all();
        try {
            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;
            $request['company_id'] = $company_id;

            if ($loginUser && !$request?->team_person_id) {
                $request['team_person_id'] = $loginUser?->id;
            }

            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'expense_category_id' => ['required', Rule::exists((new ExpenseCategory())->getTable(), 'id')]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $data = new ExpenseSubCategory();
            $data = $data->with(['company', 'branch']);
            $data = $data->where('company_id', $request?->company_id);
            $data = $data->where('expense_category_id', $request?->expense_category_id);

            if ($request?->branch_id) {
                $data = $data->where('branch_id', $request?->branch_id);
            }


            /* Belong to Team Person */

            if ($request?->team_person_id) {
                // $data->whereRaw('team_person_ids', $request?->team_person_ids);
                $data = $data->whereRaw("find_in_set('" . $request?->team_person_id . "',team_person_ids)");
                // dd($data->toSql());
            }

            if ($request?->filter_by_status) {
                if ($request?->filter_by_status != "all") {
                    $data = $data->where('status', $request?->filter_by_status);
                }
            } else {
                $data = $data->where('status', 'active');
            }

            $data = $data->orderBy('name', 'ASC');
            $data = $data->get();

            $data = $data->map(function ($row) use ($request) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['name'] = $row?->name . "";
                $temp['company_id'] = $row?->company_id . "";
                $temp['company_name'] = $row?->company?->company_name . "";
                if ($request?->team_person_id) {
                    $temp['team_person_ids'] = $row?->team_person_ids . "";
                    $temp['expense_category_id'] = $row?->expense_category_id . "";
                    $temp['expense_type'] = $row?->expense_type . "";
                    $temp['is_image_required'] = $row?->is_image_required . "";
                    $temp['min_amount'] = $row?->min_amount . "";
                    $temp['max_amount'] = $row?->max_amount . "";
                    $temp['per_km_rate'] = $row?->per_km_rate . "";
                    $temp['fix_amount'] = $row?->fix_amount . "";
                    $temp['from_time'] = $row?->from_time . "";
                    $temp['to_time'] = $row?->to_time . "";
                    $temp['status'] = $row?->status . "";
                }
                return $temp;
            });
            return $this->sendResponse($data, "Expense Category List.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * Expense add / edit
     * company_id is Required
     * expense_category_id is Required
     * team_person_id when fetch the sub category belongs to team person
     * */
    public function expense_add_edit(Request $request)
    {
        try {

            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;
            $request['company_id'] = $company_id;

            $loginUserId = $loginUser?->id;


            $rules = [
                'edit_id' => [
                    'nullable',
                    Rule::exists((new Expense())->getTable(), 'id')
                        ->where(function ($query) use ($request) {
                            return $query->where('company_id', $request?->company_id);
                        })->where(function ($query) use ($loginUserId) {
                            return $query->where('created_by', $loginUserId);
                        }),
                ],
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'expense_category_id' => ['required', Rule::exists((new ExpenseCategory())->getTable(), 'id')],
                'expense_subcategory_id' => [
                    'required',
                    Rule::exists((new ExpenseSubCategory())->getTable(), 'id')->where(function ($query) use ($request) {
                        return $query->where('expense_category_id', $request->expense_category_id);
                    })
                ],
                'date' => ['required', 'date', 'date_format:Y-m-d'],
                'req_amount' => ['required', 'numeric', 'min:0'],
                'remark' => ['nullable'],
            ];
            // dd($rules);

            $subcategory = ExpenseSubCategory::find($request?->expense_subcategory_id);
            if ($subcategory && $subcategory->is_image_required == 1) {
                $rules['attachment'] = [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,pdf,doc,docx',
                    'max:10240', // Max 10MB
                ];
            } else {
                $rules['attachment'] = [
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,pdf,doc,docx',
                    'max:10240', // Max 10MB
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }


            $input = $request->all();
            $input['status'] = 'pending';
            $input['team_person_id'] = $loginUser?->id;
            $input['created_by'] = $loginUser?->id;
            if ($request->hasFile('attachment')) {
                // return $image_name = Helper::make_slug('leave-application ' . (string)$loginUser?->id. ' '. (string)$loginUser?->name . ' ' . date('Ymd-His'));
                $image_name = Helper::make_slug(date('Ymd-His') . ' ' . (string) $loginUser?->id . ' ' . (string) $loginUser?->name);

                $file = $request->file('attachment');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $sub_folder_path = Helper::fileUploadPath($loginUser, Expense::$folderPath);
                $uploadedPath = public_path($sub_folder_path);
                // return $uploadedPath;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                    // $filename = $uploadedImage;
                    // $image_path = $sub_folder_path . '/' . $filename;
                }
                $input['attachment'] = $uploadedImage;
            }
            // return $input;
            $data = [];
            if ($request?->edit_id) {
                $data = Expense::where('id', $request?->edit_id)->first();
                if (!$data) {
                    return $this->sendError("Expense not found");
                } else if ($data?->status && $data?->status != 'pending') {
                    return $this->sendError("Your expense is " . $data?->status . ", so you can't update");
                }

                $data?->update($input);
                $data = Expense::where('id', $request?->edit_id)->first();

                return $this->sendResponse($data, "Expense update.");
            } else {
                $data = Expense::create($input);

                // Notify Manager
                if ($loginUser->parent_id) {
                    $managerNotificationData = [
                        'company_id' => $loginUser->company_id,
                        'user_id' => $loginUser->parent_id,
                        'user_type' => 'Team',
                        'title' => 'New Expense Application',
                        'body' => ($loginUser->proper_name ?? $loginUser->name) . " has applied for an expense of ₹" . $data->req_amount,
                        'module_name' => 'Expense',
                        'module_id' => $data->id,
                        'module_action' => 'pending',
                        'notify_read' => 0,
                        'status' => 'active',
                        'send_status' => 'pending',
                        'created_type' => 'Team',
                        'created_by' => $loginUser->id,
                    ];
                    Helper::sendPushNotification($managerNotificationData);
                }
            }

            return $this->sendResponse($data, "Expense add.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }


    /**
     * Expense List
     * company_id is nullable when token is exist
     ******************* Filter
     * expense_category_id
     * expense_subcategory_id
     */
    public function expense_list(Request $request)
    {
        try {

            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            if ($loginUser?->company_id) {
                $request['company_id'] = $loginUser?->company_id;
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'from_date' => ['nullable', 'date', 'date_format:Y-m-d'],
                'to_date' => ['nullable', 'date', 'date_format:Y-m-d']
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            /* Pagination */
            $pagination = (isset($request?->page)) ? true : false;
            $perPage = (isset($request?->per_page)) ? $request?->per_page : env("API_PER_PAGE");
            $page = (isset($request?->page)) ? $request?->page : 1;

            $moduleDocuments = Expense::where('company_id', $company_id)->where('team_person_id', $loginUser?->id);

            if ($request?->expense_category_id) {
                $moduleDocuments = $moduleDocuments->where('expense_category_id', $request?->expense_category_id);
            }
            if ($request?->expense_subcategory_id) {
                $moduleDocuments = $moduleDocuments->where('expense_subcategory_id', $request?->expense_subcategory_id);
            }

            if ($request?->from_date && !$request?->to_date) {
                // dd('Expense 280', $request?->from_date, $request?->to_date);
                // $moduleDocuments = $moduleDocuments->whereDate('from_date', "<=", Carbon::parse($request?->from_date))->whereDate('to_date', ">=", Carbon::parse($request?->from_date));

                $moduleDocuments = $moduleDocuments->where(function ($query) use ($request) {
                    $fromdateTime = Carbon::parse($request->from_date)->toDateString();
                    $query->whereDate('date', '==', $fromdateTime);
                });
            } else if ($request?->from_date && $request?->to_date) {

                $filter_start = Carbon::parse($request->from_date)->toDateString();
                $filter_end = Carbon::parse($request->to_date)->toDateString();
                $moduleDocuments = $moduleDocuments->where('date', '>=', $filter_start)->where('date', '<=', $filter_end);

                // dd('Expense 287', $request?->from_date, $request?->to_date, Helper::interpolateQuery($moduleDocuments->toSql(), $moduleDocuments->getBindings()));
            }
            // dd('Expense 307', Helper::interpolateQuery($moduleDocuments->toSql(), $moduleDocuments->getBindings()), $moduleDocuments->first(), $loginUser->toArray());
            $moduleDocuments = $moduleDocuments->orderBy('id', 'DESC');

            $tempResponseData = [];
            if ($pagination) {
                $moduleDocuments = $moduleDocuments->paginate($perPage);

                $tempResponseData['total'] = "" . $moduleDocuments->total();
                $tempResponseData['perPage'] = $perPage;
                $tempResponseData['currentPage'] = $page;
                $tempResponseData['totalPage'] = "" . $moduleDocuments->lastPage();
                $tempResponseData['items'] = (object) $moduleDocuments->items();
                $tempResponseData['items'] = collect($tempResponseData['items']);
            } else {
                $moduleDocuments = $moduleDocuments->get();
                $tempResponseData['total'] = $moduleDocuments->count();
                $tempResponseData['items'] = $moduleDocuments;
            }

            if (isset($tempResponseData['items'])) {
                $tempResponseData['items'] = $tempResponseData['items']->map(function ($record) {
                    $temp = [];
                    // $temp = $record;
                    $temp['id'] = $record?->id . "";
                    $temp['company_id'] = $record?->company_id . "";
                    $temp['company_name'] = $record?->company?->company_name . "";

                    $temp['team_person_id'] = $record?->team_person_id . "";
                    $temp['team_person_name'] = $record?->team_person?->name . "";

                    $temp['expense_category_id'] = $record?->expense_category_id . "";
                    $temp['expense_category_name'] = $record?->expense_category?->name . "";

                    $temp['expense_subcategory_id'] = $record?->expense_subcategory_id . "";
                    $temp['expense_subcategory_name'] = $record?->expense_sub_category?->name . "";

                    $temp['date'] = $record?->date . "";
                    $temp['req_amount'] = $record?->req_amount . "";
                    $temp['pass_amount'] = ($record?->pass_amount !== null) ? $record?->pass_amount . "" : "0.00";
                    $temp['reject_amount'] = ($record?->status == 'reject') ? $record?->req_amount . "" : (($record?->status == 'pass') ? ($record?->req_amount - $record?->pass_amount) . "" : "0.00");
                    $temp['remark'] = $record?->remark . "";
                    $temp['reason'] = $record?->reason . "";
                    $temp['attachment'] = $record?->attachment . "";
                    $temp['attachment_url'] = $record?->attachment_url . "";
                    $temp['status'] = $record?->status . "";
                    $temp['created_by'] = $record?->created_by . "";
                    return $temp;
                });

                return $this->sendResponse($tempResponseData, "Your Expense List.");
            }
            return $this->sendError("Something want to wrong in data fetching.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * Expense delete
     * company_id is nullable when token is exist
     * expense_id
     */
    public function expense_delete(Request $request)
    {
        try {

            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            if ($loginUser?->company_id) {
                $request['company_id'] = $loginUser?->company_id;
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'id' => ['required', Rule::exists((new Expense())->getTable(), 'id')],
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }


            $moduleDocuments = Expense::where('company_id', $company_id)->where('team_person_id', $loginUser?->id);
            $moduleDocuments = $moduleDocuments->where('id', $request?->id);
            $moduleDocuments = $moduleDocuments->orderBy('id', 'DESC');
            $moduleDocuments = $moduleDocuments->first();

            if ($moduleDocuments) {
                if ($moduleDocuments?->status != "pending") {
                    return $this->sendError("You can not delete this Expense. reason is your Expense current status is " . ucfirst($moduleDocuments->status));
                }
                if ($moduleDocuments->delete()) {
                    return $this->sendResponse($moduleDocuments, "Your Expense Delete Successfully.");
                }
                return $this->sendError("Your Expense found, but can not deleted.");
            }

            return $this->sendError("Your Expense not found.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }


    /**
     * Expense chart
     * company_id is Required
     */
    public function expense_chart(Request $request)
    {
        try {

            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            if ($loginUser?->company_id) {
                $request['company_id'] = $loginUser?->company_id;
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'from_date' => ['nullable', 'date', 'date_format:Y-m-d'],
                'to_date' => ['nullable', 'date', 'date_format:Y-m-d']
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }


            $moduleDocuments = Expense::where('company_id', $company_id)->where('team_person_id', $loginUser?->id);

            if ($request?->expense_category_id) {
                $moduleDocuments = $moduleDocuments->where('expense_category_id', $request?->expense_category_id);
            }
            if ($request?->expense_subcategory_id) {
                $moduleDocuments = $moduleDocuments->where('expense_subcategory_id', $request?->expense_subcategory_id);
            }

            /*
            if ($request?->from_date && !$request?->to_date) {
                // dd('Expense 280', $request?->from_date, $request?->to_date);
                // $moduleDocuments = $moduleDocuments->whereDate('from_date', "<=", Carbon::parse($request?->from_date))->whereDate('to_date', ">=", Carbon::parse($request?->from_date));

                $moduleDocuments = $moduleDocuments->where(function ($query) use ($request) {
                    $fromdateTime = Carbon::parse($request->from_date)->toDateString();
                    $query->whereDate('date', '==', $fromdateTime);
                });
            } else if ($request?->from_date && $request?->to_date) {

                $filter_start = Carbon::parse($request->from_date)->toDateString();
                $filter_end = Carbon::parse($request->to_date)->toDateString();
                $moduleDocuments = $moduleDocuments->whereDate('date', '>=', $filter_start)->whereDate('date', '<=', $filter_end);

                // dd('Expense 287', $request?->from_date, $request?->to_date, Helper::interpolateQuery($moduleDocuments->toSql(), $moduleDocuments->getBindings()));
            }
                */
            // dd('Expense 307', Helper::interpolateQuery($moduleDocuments->toSql(), $moduleDocuments->getBindings()), $moduleDocuments->first(), $loginUser->toArray());
            $moduleDocuments = $moduleDocuments->orderBy('id', 'DESC');

            $tempResponseData = [];
            $moduleDocuments = $moduleDocuments->get();


            // $tempResponseData['total'] = $moduleDocuments->count()."";
            // $tempResponseData['items'] = $moduleDocuments;

            $now = Carbon::now();
            $fromDate = ($request->from_date) ? Carbon::parse($request->from_date) : $now;
            $toDate = ($request->to_date) ? Carbon::parse($request->to_date) : $now;

            $currentMonthStart = $fromDate->copy()->startOfMonth();
            $currentMonthEnd = $toDate->copy()->endOfMonth();
            $previousMonthStart = $fromDate->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $toDate->copy()->subMonth()->endOfMonth();


            $currentMonthExpence = $moduleDocuments->whereBetween('date', [$currentMonthStart, $currentMonthEnd]);
            $previousMonthExpence = $moduleDocuments->whereBetween('date', [$previousMonthStart, $previousMonthEnd]);

            // dd($now, $currentMonthStart, $currentMonthEnd, $previousMonthStart, $previousMonthEnd);

            $tempResponseData['current_month_total_expence'] = $currentMonthExpence->sum('req_amount') . "";
            $tempResponseData['previous_month_total_expence'] = $previousMonthExpence->sum('req_amount') . "";

            $tempResponseData['current_month_total_expence_passed'] = $currentMonthExpence->where('status', 'pass')->sum('pass_amount') . "";
            $tempResponseData['previous_month_total_expence_passed'] = $previousMonthExpence->where('status', 'pass')->sum('pass_amount') . "";

            $tempResponseData['current_month_total_expence_pending'] = $currentMonthExpence->where('status', 'pending')->sum('req_amount') . "";
            $tempResponseData['previous_month_total_expence_pending'] = $previousMonthExpence->where('status', 'pending')->sum('req_amount') . "";

            $tempResponseData['current_month_passed_percent'] = self::percent($tempResponseData['current_month_total_expence_passed'], $tempResponseData['current_month_total_expence']) . "";
            $tempResponseData['current_month_pending_percent'] = self::percent($tempResponseData['current_month_total_expence_pending'], $tempResponseData['current_month_total_expence']) . "";

            $tempResponseData['previous_month_passed_percent'] = self::percent($tempResponseData['previous_month_total_expence_passed'], $tempResponseData['previous_month_total_expence']) . "";
            $tempResponseData['previous_month_pending_percent'] = self::percent($tempResponseData['previous_month_total_expence_pending'], $tempResponseData['previous_month_total_expence']) . "";



            return $this->sendResponse($tempResponseData, "Your Expense Chart.");
            return $this->sendError("Something want to wrong in data fetching.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    public function percent($part, $total)
    {
        return $total > 0 ? round(($part / $total) * 100, 2) : 0;
    }

    /**
     * Expense Category Add/Edit
     */
    public function expense_category_add_edit(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'edit_id' => ['nullable', Rule::exists((new ExpenseCategory())->getTable(), 'id')->where('company_id', $company_id)],
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'name' => ['required', 'string', 'max:255'],
                'status' => ['nullable', 'in:active,inactive']
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $input = $request->all();
            $input['company_id'] = $company_id;
            $input['created_by'] = $loginUser?->id;
            $input['updated_by'] = $loginUser?->id;

            if ($request?->edit_id) {
                $category = ExpenseCategory::find($request->edit_id);
                $category->update($input);
                return $this->sendResponse($category, "Expense Category updated successfully.");
            } else {
                $category = ExpenseCategory::create($input);
                return $this->sendResponse($category, "Expense Category created successfully.");
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Expense Category Delete
     */
    public function expense_category_delete(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'id' => ['required', Rule::exists((new ExpenseCategory())->getTable(), 'id')->where('company_id', $company_id)]
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $category = ExpenseCategory::find($request->id);
            $category->delete();

            return $this->sendResponse(null, "Expense Category deleted successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Expense Sub-Category Add/Edit
     */
    public function expense_sub_category_add_edit(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'edit_id' => ['nullable', Rule::exists((new ExpenseSubCategory())->getTable(), 'id')->where('company_id', $company_id)],
                'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
                'expense_category_id' => ['required', Rule::exists((new ExpenseCategory())->getTable(), 'id')],
                'name' => ['required', 'string', 'max:255'],
                'expense_type' => ['required', 'in:General,KM,Food'],
                'min_amount' => ['nullable', 'numeric'],
                'max_amount' => ['nullable', 'numeric'],
                'status' => ['nullable', 'in:active,inactive'],
                'team_person_ids' => ['nullable', 'string'] // comma separated IDs
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $input = $request->all();
            $input['company_id'] = $company_id;
            $input['created_by'] = $loginUser?->id;
            $input['updated_by'] = $loginUser?->id;

            if ($request?->edit_id) {
                $subCategory = ExpenseSubCategory::find($request->edit_id);
                $subCategory->update($input);
                return $this->sendResponse($subCategory, "Expense Sub-Category updated successfully.");
            } else {
                $subCategory = ExpenseSubCategory::create($input);
                return $this->sendResponse($subCategory, "Expense Sub-Category created successfully.");
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Expense Sub-Category Delete
     */
    public function expense_sub_category_delete(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'id' => ['required', Rule::exists((new ExpenseSubCategory())->getTable(), 'id')->where('company_id', $company_id)]
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $subCategory = ExpenseSubCategory::find($request->id);
            $subCategory->delete();

            return $this->sendResponse(null, "Expense Sub-Category deleted successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
    /**
     * Expense Status Update (Approve/Reject)
     * For Admin/Managers to approve or reject expenses via API
     */
    public function expense_status_update(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'id' => ['required', Rule::exists((new Expense())->getTable(), 'id')],
                'update_status' => ['required', 'in:pass,reject'],
                'pass_amount' => ['nullable', 'numeric', 'min:0'],
                'reason' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $expense = Expense::find($request->id);

            // Basic security check: ensure it belongs to the same company
            if ($expense->company_id != $loginUser->company_id) {
                return $this->sendError("You don't have permission to update this expense.", [], [], 403);
            }

            $expense->status = $request->update_status;
            if ($request->update_status == 'pass') {
                $expense->pass_amount = $request->pass_amount ?? $expense->req_amount;
            } else if ($request->update_status == 'reject') {
                $expense->pass_amount = $request->pass_amount ?? 0;
            }
            $expense->reason = $request->reason;
            $expense->updated_by = $loginUser->id;
            $expense->save();

            $approvedBy = $loginUser?->name ?? 'Admin';
            $amount = $expense->req_amount;

            if ($expense->status === 'pass') {
                $body = "Your Expense Request of ₹{$amount} has been Approved by {$approvedBy}.";
            } else {
                $body = "Your Expense Request of ₹{$amount} has been Rejected by {$approvedBy}.\n\nReason: " . ($expense->reason ?? 'N/A');
            }

            // Send Push Notification and Add to DB
            $notificationData = [
                'company_id' => $expense->company_id,
                'user_id' => $expense->team_person_id,
                'user_type' => 'Team',
                'title' => 'Expense Application ' . ($expense->status == 'pass' ? 'Approved' : 'Rejected'),
                'body' => $body,
                'module_name' => 'Expense',
                'module_id' => $expense->id,
                'module_action' => $expense->status,
                'notify_read' => 0,
                'status' => 'active',
                'send_status' => 'pending',
                'created_type' => 'Admin',
                'created_by' => $loginUser->id,
            ];
            Helper::sendPushNotification($notificationData);

            return $this->sendResponse($expense, "Expense status updated successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
