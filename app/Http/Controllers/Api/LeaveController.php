<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    public function __construct(Request $request)
    {
    }

    /**
     * Leave Type List
     * company_id is nullable when token is exist
     * filter_by_status is all, active, inactive
     */
    public function leave_type_list(Request $request)
    {
        try {

            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ]
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $data = new LeaveType();
            $data = $data->with(['company']);
            $data = $data->where('company_id', $company_id);

            if ($request?->filter_by_status) {
                if ($request?->filter_by_status != "all") {
                    $data = $data->where('status', $request?->filter_by_status);
                }
            } else {
                $data = $data->where('status', 'active');
            }

            $data = $data->orderBy('full_name', 'ASC');
            $data = $data->get();
            // return $data;
            $data = $data->map(function ($row) {
                // $temp = $row;
                $temp['id'] = $row?->id . "";
                $temp['full_name'] = $row?->full_name . "";
                $temp['short_name'] = $row?->sort_name . "";
                $temp['company_id'] = $row?->company_id . "";
                $temp['company_name'] = $row?->company->company_name . "";
                return $temp;
            });
            return $this->sendResponse($data, "Leave Type List.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * Leave add / Edit
     * company_id is nullable when token is exist
     */
    public function leave_add_edit(Request $request)
    {
        try {

            $loginUser = Auth::user();
            if (!$loginUser && !$request?->company_id) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            if ($loginUser?->company_id) {
                $request['company_id'] = $loginUser?->company_id;
            }
            $request['employee_id'] = $loginUser?->id ?? '';
            $company_id = $request?->company_id ?? $loginUser?->company_id;

            $validator = Validator::make($request->all(), [
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ],
                'edit_id' => [
                    'nullable',
                    Rule::exists((new LeaveApplication())->getTable(), 'id')
                        ->where(function ($query) use ($company_id) {
                            $query->where('company_id', $company_id);
                        }),
                ],
                'leave_type_id' => [
                    'required',
                    Rule::exists((new LeaveType())->getTable(), 'id'),
                ],
                'halfday_fullday' => [
                    'required',
                    'in:' . implode(",", array_keys(LeaveApplication::$leaveForDay)),
                ],
                // 'singleday_multipleday' => [
                //     'required_if:halfday_fullday,fullday',
                //     'in:' . implode(",", array_keys(LeaveApplication::$leaveByDays)),
                // ],
                'firsthalf_secondhalf' => [
                    'required_if:halfday_fullday,halfday',
                    'nullable',
                    'in:' . implode(",", array_keys(LeaveApplication::$leaveForHalfdays)),
                ],
                'fromdate_time' => [
                    'required',
                    'date',
                    'date_format:Y-m-d H:i:s',
                    'after_or_equal:' . now()->format('Y-m-d H:i:s')
                ],
                'todate_time' => [
                    // 'required_unless:halfday_fullday,halfday',
                    'required_if:singleday_multipleday,multipleday',
                    // 'nullable',
                    'date',
                    'date_format:Y-m-d H:i:s',
                    'after_or_equal:' . $request?->fromdate_time
                ],
                'leave_reason' => [
                    'required',
                    'string',
                    'max:1000',
                ],
                'attachment' => [
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,pdf,doc,docx',
                    'max:10240', // Max 10MB
                ],
            ]);

            $validator->sometimes('fromdate_time', 'after_or_equal:' . now()->startOfDay()->format('Y-m-d H:i:s'), function ($input) {
                return empty($input->edit_id);
            });

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $fromdateTime = (int) Helper::convert_date($request?->fromdate_time, "Y-m-d H:i:s", "YmdHms");

            $minStartTime = $loginUser->min_working_start_time ?? '09:00:00';

            $fromDate = \Carbon\Carbon::parse($request?->fromdate_time)->toDateString();

            // Add 4 hours to min_working_start_time
            $minStartWithBuffer = \Carbon\Carbon::parse($fromDate . ' ' . $minStartTime)->addHours(4);
            $minStartTime = \Carbon\Carbon::parse($fromDate . ' ' . $minStartTime);

            $minStartTime = (int) Helper::convert_date($minStartTime, "Y-m-d H:i:s", "YmdHms");
            $minStartWithBufferInt = (int) Helper::convert_date($minStartWithBuffer, "Y-m-d H:i:s", "YmdHms");

            /** First Half Leavel Added Before the Punch In Time */
            /*
            if ($request->firsthalf_secondhalf === 'firsthalf' && $minStartTime > $fromdateTime) {
                dd("L-158", $fromdateTime, $minStartTime, $minStartWithBuffer, $minStartWithBuffer);
            } else if ($request->firsthalf_secondhalf === 'secondhalf') {
                if ($minStartTime > $fromdateTime) {
                    return $this->sendError("Your Secod Half Leave after the ".$loginUser->min_working_start_time);
                }else if($minStartWithBufferInt > $fromdateTime){
                    return $this->sendError("You can not add the leave of current second half");
                    dd("L-165", $request->firsthalf_secondhalf, $fromdateTime, $minStartTime, $minStartWithBufferInt.' < '.$fromdateTime, $loginUser->min_working_start_time);
                }
            }
            dd("L-168", $request->firsthalf_secondhalf, $fromdateTime, $minStartTime, $minStartWithBuffer, $minStartTime . ' < ' . $fromdateTime, $minStartTime < $fromdateTime, $fromdateTime > $minStartWithBufferInt);
            */

            // --- ALL NEW LOGIC ADDED FOR ATTACHMENT, CARRY FORWARD, MONTHLY LIMIT & C-OFF ---
            $edit_id = $request->edit_id ?? 0;
            $employee_id = $loginUser->id;
            $leave_type_id = $request->leave_type_id;
            $reqFromDateTime = $request->fromdate_time;
            $reqToDateTime = $request->todate_time;
            $halfFullDay = $request->halfday_fullday;

            if ($leave_type_id && $reqFromDateTime) {
                $leaveType = LeaveType::find($leave_type_id);
                if ($leaveType) {
                    $selectedDays = 0.5; // default for halfday
                    if ($halfFullDay === 'fullday') {
                        try {
                            $fDate = \Carbon\Carbon::parse($reqFromDateTime)->startOfDay();
                            $tDate = $reqToDateTime ? \Carbon\Carbon::parse($reqToDateTime)->startOfDay() : $fDate;
                            $selectedDays = $fDate->diffInDays($tDate) + 1;
                        } catch (\Exception $e) {
                            $selectedDays = 1;
                        }
                    }

                    // 1. Attachment required days validation
                    if ($leaveType->attachment_required == 1 || $leaveType->attachment_required_days > 0) {
                        $isMandatory = ($leaveType->attachment_required == 1) || ($leaveType->attachment_required_days > 0 && $selectedDays > $leaveType->attachment_required_days);
                        
                        if ($isMandatory) {
                            $hasFile = $request->hasFile('attachment');
                            $alreadyHasFile = false;
                            if ($edit_id) {
                                $leaveApp = LeaveApplication::find($edit_id);
                                if ($leaveApp && $leaveApp->attachment) {
                                    $alreadyHasFile = true;
                                }
                            }
                            if (!$hasFile && !$alreadyHasFile) {
                                $errorMsg = ($leaveType->attachment_required == 1)
                                    ? 'Attachment is mandatory for ' . $leaveType->full_name . '.'
                                    : 'Attachment is mandatory because leave duration (' . $selectedDays . ' days) exceeds the limit of ' . $leaveType->attachment_required_days . ' days for ' . $leaveType->full_name . '.';
                                return $this->sendError($errorMsg, [], [], 422);
                            }
                        }
                    }

                    // 2. Carry Forward leave limit validation (Max 7 Days)
                    if ($leaveType->carry_forward == 1) {
                        if ($selectedDays > 7) {
                            return $this->sendError('Carry Forward leaves cannot be taken for more than 7 days in a single application.', [], [], 422);
                        }
                    }

                    // 3. Monthly leave limit validation
                    if ($leaveType->count > 0 && $employee_id) {
                        try {
                            $fromDateObj = \Carbon\Carbon::parse($reqFromDateTime);
                            $targetYear = $fromDateObj->year;
                            $targetMonth = $fromDateObj->month;

                            $monthlyAccrual = (float)$leaveType->count / 12;

                            if ($leaveType->carry_forward == 1) {
                                if ($targetMonth >= 4) {
                                    $completedMonths = $targetMonth - 4;
                                    $fyStart = \Carbon\Carbon::create($targetYear, 4, 1)->startOfDay();
                                } else {
                                    $completedMonths = $targetMonth + 8;
                                    $fyStart = \Carbon\Carbon::create($targetYear - 1, 4, 1)->startOfDay();
                                }

                                $completedMonthsAccrual = $monthlyAccrual * $completedMonths;
                                $currentMonthAccrual = ($fromDateObj->day >= 15) ? $monthlyAccrual : ($monthlyAccrual / 2.0);
                                $accumulatedLimit = $completedMonthsAccrual + $currentMonthAccrual;
                                $accumulatedLimit = round($accumulatedLimit * 2) / 2;

                                $targetMonthEnd = $fromDateObj->copy()->endOfMonth();

                                $existingDaysQuery = LeaveApplication::where('employee_id', $employee_id)
                                    ->where('leave_type_id', $leave_type_id)
                                    ->where('status', '!=', 'rejected')
                                    ->whereBetween('fromdate_time', [$fyStart, $targetMonthEnd]);

                                if ($edit_id) {
                                    $existingDaysQuery->where('id', '!=', $edit_id);
                                }

                                $existingApps = $existingDaysQuery->get();
                                $totalExistingDays = 0;
                                foreach ($existingApps as $app) {
                                    $days = 0.5;
                                    if ($app->halfday_fullday === 'fullday') {
                                        try {
                                            $fDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                                            $tDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fDate;
                                            $days = $fDate->diffInDays($tDate) + 1;
                                        } catch (\Exception $e) {
                                            $days = 1;
                                        }
                                    }
                                    $totalExistingDays += $days;
                                }

                                $totalRequestedDays = $totalExistingDays + $selectedDays;

                                if ($totalRequestedDays > $accumulatedLimit) {
                                    return $this->sendError("As Carry Forward is enabled for {$leaveType->full_name}, you have accrued a maximum of {$accumulatedLimit} leaves up to this month (Accrual rate: {$monthlyAccrual} per month). You have already taken/applied for {$totalExistingDays} days in or before this month.", [], [], 422);
                                }
                            } else {
                                $existingDaysQuery = LeaveApplication::where('employee_id', $employee_id)
                                    ->where('leave_type_id', $leave_type_id)
                                    ->where('status', '!=', 'rejected')
                                    ->whereYear('fromdate_time', $targetYear)
                                    ->whereMonth('fromdate_time', $targetMonth);

                                if ($edit_id) {
                                    $existingDaysQuery->where('id', '!=', $edit_id);
                                }

                                $existingApps = $existingDaysQuery->get();
                                $totalExistingDays = 0;
                                foreach ($existingApps as $app) {
                                    $days = 0.5;
                                    if ($app->halfday_fullday === 'fullday') {
                                        try {
                                            $fDate = \Carbon\Carbon::parse($app->fromdate_time)->startOfDay();
                                            $tDate = $app->todate_time ? \Carbon\Carbon::parse($app->todate_time)->startOfDay() : $fDate;
                                            $days = $fDate->diffInDays($tDate) + 1;
                                        } catch (\Exception $e) {
                                            $days = 1;
                                        }
                                    }
                                    $totalExistingDays += $days;
                                }

                                $totalRequestedDays = $totalExistingDays + $selectedDays;

                                if ($totalRequestedDays > $monthlyAccrual) {
                                    return $this->sendError("As Carry Forward is not enabled for {$leaveType->full_name}, you can only take a maximum of {$monthlyAccrual} leaves in a single month (Total Count {$leaveType->count} / 12). You have already taken/applied for {$totalExistingDays} days in this month.", [], [], 422);
                                }
                            }
                        } catch (\Exception $e) {
                            // Skip if date parsing failed
                        }
                    }

                    // 4. C-Off leave balance validation
                    if (strtolower($leaveType->sort_name) === 'c-off' || strtolower($leaveType->sort_name) === 'coff' || strtolower($leaveType->full_name) === 'compensatory off') {
                        $employee = \App\Models\Employee::find($employee_id);
                        if ($employee) {
                            $available = $employee->getAvailableCoffCount($reqFromDateTime, $edit_id);
                            if ($selectedDays > $available) {
                                return $this->sendError("You only have {$available} Compensatory Off (C-Off) leaves available. You are trying to apply for {$selectedDays} days.", [], [], 422);
                            }
                        }
                    }

                    // 5. Available leave balance validation (same logic as displayed balance)
                    $employee = \App\Models\Employee::with('employmentDetail')->find($employee_id);
                    if ($employee) {
                        $availableBalance = $employee->getAvailableLeaveBalance($leave_type_id, $edit_id);
                        if ($selectedDays > $availableBalance) {
                            return $this->sendError("You only have {$availableBalance} {$leaveType->full_name} days available. You are trying to apply for {$selectedDays} days.", [], [], 422);
                        }
                    }
                }
            }
            // --- END NEW LOGIC ---

            // Custom after validation to prevent duplicate leave on same date/time
            $checkLeaveApplicationExists = LeaveApplication::where('company_id', $company_id)->where('employee_id', $loginUser?->id);

            if ($request?->edit_id) {
                $checkLeaveApplicationExists = $checkLeaveApplicationExists->where('id', '!=', $request->edit_id);
            }
            /** */
            $errorMessage = "";
            if ($request?->halfday_fullday == "halfday") {
                $checkLeaveApplicationExists = $checkLeaveApplicationExists->where('halfday_fullday', $request?->halfday_fullday);
                $checkLeaveApplicationExists = $checkLeaveApplicationExists->where('firsthalf_secondhalf', $request?->firsthalf_secondhalf);
                // $checkLeaveApplicationExists = $checkLeaveApplicationExists->whereDate('fromdate_time', $request?->fromdate_time);
                $checkLeaveApplicationExists = $checkLeaveApplicationExists->whereDate('fromdate_time', Carbon::parse($request->fromdate_time)->toDateString());

                /*
                $minStartTime = $loginUser->min_working_start_time ?? '09:00:00';

                // Add 4 hours to min_working_start_time
                $minStartWithBuffer = \Carbon\Carbon::parse($fromDate . ' ' . $minStartTime)->addHours(4);

                if ($request->firsthalf_secondhalf === 'firsthalf') {
                    $checkLeaveApplicationExists = $checkLeaveApplicationExists->whereTime('fromdate_time', '<=', $minStartTime);
                } else if ($request->firsthalf_secondhalf === 'secondhalf') {
                    $checkLeaveApplicationExists = $checkLeaveApplicationExists->whereTime('fromdate_time', '>=', $minStartTime)->whereTime('fromdate_time', '<=', $minStartWithBuffer);
                }
                */
            } else if ($request?->halfday_fullday == "fullday") {
                // $checkLeaveApplicationExists = $checkLeaveApplicationExists->where('halfday_fullday', $request?->halfday_fullday);
                // $checkLeaveApplicationExists = $checkLeaveApplicationExists->whereDate('fromdate_time', ">=", Carbon::parse($request->fromdate_time)->toDateString());
                if ($request?->singleday_multipleday == "multipleday") {
                    /*
                    $checkLeaveApplicationExists = $checkLeaveApplicationExists->where(function ($query) use ($request) {
                        $fromdateTime = Carbon::parse($request->fromdate_time)->toDateString();
                        $query->whereDate('fromdate_time', '<=', $fromdateTime)->whereDate('todate_time', '>=', $fromdateTime);
                    });
                    $checkLeaveApplicationExists = $checkLeaveApplicationExists->orWhere(function ($query) use ($request) {
                        $todateTime = Carbon::parse($request->todate_time)->toDateString();
                        $query->whereDate('fromdate_time', '<=', $todateTime)->whereDate('todate_time', '>=', $todateTime);
                    });
                    */
                    $checkLeaveApplicationExists = $checkLeaveApplicationExists->where(function ($query) use ($request) {
                        $fromdateTime = Carbon::parse($request->fromdate_time)->toDateString();
                        $todateTime = Carbon::parse($request->todate_time)->toDateString();

                        $query->whereBetween('fromdate_time', [$fromdateTime, $todateTime])
                            ->orWhereBetween('todate_time', [$fromdateTime, $todateTime])
                            ->orWhere(function ($q) use ($fromdateTime, $todateTime) {
                                $q->where('fromdate_time', '<=', $fromdateTime)
                                    ->where('todate_time', '>=', $todateTime);
                            });
                    });
                    $errorMessage = "Leave Exist on this date " . Carbon::parse($request->fromdate_time)->format("d-m-Y") . ' to ' . Carbon::parse($request->todate_time)->format("d-m-Y") . '. Select other date.';
                } else {
                    $checkLeaveApplicationExists = $checkLeaveApplicationExists->whereDate('fromdate_time', '<=', Carbon::parse($request->fromdate_time)->toDateString())->whereDate('todate_time', '>=', Carbon::parse($request->fromdate_time)->toDateString());
                    $errorMessage = "Leave Exist on this date " . Carbon::parse($request->fromdate_time)->format("d-m-Y") . '. Select other date.';
                }
            } else {
                $checkLeaveApplicationExists = $checkLeaveApplicationExists->where('id', "0");
            }
            // dd('checkLeaveApplicationExists 228', Helper::interpolateQuery($checkLeaveApplicationExists->toSql(), $checkLeaveApplicationExists->getBindings()), $checkLeaveApplicationExists->first(), $loginUser->toArray());
            $checkLeaveApplicationExists = $checkLeaveApplicationExists->first();

            if ($checkLeaveApplicationExists) {
                if ($request?->halfday_fullday == "halfday") {
                    if ($request->firsthalf_secondhalf === 'firsthalf') {
                        return $this->sendError("All ready add leave on this date(" . Helper::convert_date($checkLeaveApplicationExists?->fromdate_time, "Y-m-d H:i:s", "d-m-Y") . ") and First Half.");
                    } else if ($request->firsthalf_secondhalf === 'secondhalf') {
                        return $this->sendError("All ready add leave on this date(" . Helper::convert_date($checkLeaveApplicationExists?->fromdate_time, "Y-m-d H:i:s", "d-m-Y") . ") and Second Half.");
                    } else {
                        return 208;
                    }
                } else if ($request?->halfday_fullday == "fullday") {
                    return $this->sendError($errorMessage);
                }
            }

            // $checkLeaveApplicationExists = $checkLeaveApplicationExists->get();

            // dd('checkLeaveApplicationExists 211', $checkLeaveApplicationExists);
            // dd('checkLeaveApplicationExists 176', Helper::interpolateQuery($checkLeaveApplicationExists->toSql(), $checkLeaveApplicationExists->getBindings()), $checkLeaveApplicationExists->first(), $loginUser->toArray());
            // return $request->all();
            //dd($request->all());
            $loginUserId = $loginUser?->id;

            $input = $request->except(['edit_id']);
            $returnMessage = "Add Leave by company.";

            $leaveApplicationInputs = $request->all();
            if ($request->hasFile('attachment')) {
                // return $image_name = Helper::make_slug('leave-application ' . (string)$loginUser?->id. ' '. (string)$loginUser?->name . ' ' . date('Ymd-His'));
                $image_name = Helper::make_slug(date('Ymd-His') . ' ' . (string) $loginUser?->id . ' ' . (string) $loginUser?->name);

                $file = $request->file('attachment');

                $extenstion = $file->getClientOriginalExtension();

                $filename = $image_name . '.' . $extenstion;

                $sub_folder_path = Helper::fileUploadPath($loginUser, LeaveApplication::$folderPath);
                $uploadedPath = public_path($sub_folder_path);

                if (!file_exists($uploadedPath)) {
                    mkdir($uploadedPath, 0777, true);
                }

                // return $uploadedPath;
                $uploadedImage = null;
                if ($file->move($uploadedPath, $filename)) {
                    $uploadedImage = $sub_folder_path . $filename;
                    if (in_array($extenstion, ["jpeg", "png", "jpg"])) {
                        $webP = Helper::existingImageConverToWebp($extenstion, $uploadedPath, $filename, $image_name, 60, true);
                        if ($webP) {
                            $uploadedImage = $sub_folder_path . $webP;
                            $extenstion = "webp";
                        }
                    }
                }
                if ($uploadedImage) {
                    $leaveApplicationInputs['attachment'] = $uploadedImage;
                }
            }
            // return $leaveApplicationInputs;
            if ($request?->edit_id) {
                $newAttachment = $leaveApplicationInputs['attachment'] ?? null;

                $leaveApplicationInputs = [];

                $leaveApplicationInputs['leave_type_id'] = ($request?->leave_type_id) ? $request?->leave_type_id : null;
                $leaveApplicationInputs['halfday_fullday'] = ($request?->halfday_fullday) ? $request?->halfday_fullday : null;
                $leaveApplicationInputs['firsthalf_secondhalf'] = ($request?->firsthalf_secondhalf) ? $request?->firsthalf_secondhalf : null;
                $leaveApplicationInputs['singleday_multipleday'] = ($request?->singleday_multipleday) ? $request?->singleday_multipleday : null;
                $leaveApplicationInputs['leave_reason'] = ($request?->leave_reason) ? $request?->leave_reason : null;
                $leaveApplicationInputs['fromdate_time'] = ($request?->fromdate_time) ? $request?->fromdate_time : null;
                $leaveApplicationInputs['todate_time'] = ($request?->todate_time) ? $request?->todate_time : null;

                if ($newAttachment) {
                    $leaveApplicationInputs['attachment'] = $newAttachment;
                }

                $leaveApplicationInputs['updated_type'] = 'Team';
                $leaveApplicationInputs['updated_by'] = $loginUserId;
                // return $leaveApplicationInputs;

                $returnMessage = "Update Leave by company.";
                $LeaveAddUpdate = LeaveApplication::where('id', $request?->edit_id);
                $LeaveAddUpdate = $LeaveAddUpdate->where('company_id', $company_id);
                $LeaveAddUpdate = $LeaveAddUpdate->first();
                if (!$LeaveAddUpdate) {
                    return $this->sendError("Your Leave Application Id not Found. Try Again.");
                }

                // Delete old attachment if a new one is uploaded
                if ($newAttachment && $LeaveAddUpdate->attachment && file_exists(public_path($LeaveAddUpdate->attachment))) {
                    @unlink(public_path($LeaveAddUpdate->attachment));
                }

                $LeaveAddUpdate->update($leaveApplicationInputs);
                $returnResponse = LeaveApplication::where('id', $request?->edit_id)->where('company_id', $company_id)->first();
            } else {
                $leaveApplicationInputs['employee_id'] = $loginUser?->id;
                $leaveApplicationInputs['status'] = 'pending';
                $leaveApplicationInputs['created_by'] = $loginUserId;
                $leaveApplicationInputs['created_type'] = 'Team';
                $returnResponse = LeaveApplication::create($leaveApplicationInputs);

                // Notify Manager
                if ($loginUser->parent_id) {
                    $startDate = Carbon::parse($returnResponse->fromdate_time)->format('d-m-Y');
                    $endDate = ($returnResponse->todate_time) ? Carbon::parse($returnResponse->todate_time)->format('d-m-Y') : $startDate;

                    $managerNotificationData = [
                        'company_id' => $loginUser->company_id,
                        'user_id' => $loginUser->parent_id,
                        'user_type' => 'Team',
                        'title' => 'New Leave Application',
                        'body' => ($loginUser->proper_name ?? $loginUser->name) . " has applied for a leave from {$startDate} to {$endDate}.",
                        'module_name' => 'Leave',
                        'module_id' => $returnResponse->id,
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


            return $this->sendResponse($returnResponse, $returnMessage);
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * Leave delete
     * company_id is nullable when token is exist
     */
    public function leave_delete(Request $request)
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
                'company_id' => [
                    'required',
                    Rule::exists((new Company())->getTable(), 'id')
                ],
                'id' => [
                    'required',
                    Rule::exists((new LeaveApplication())->getTable(), 'id'),
                ],
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $leavelApplication = LeaveApplication::where('company_id', $company_id)->where('id', $request?->id)->first();

            if (!$leavelApplication) {
                return $this->sendError("Your Application not found contect to admin.");
            }
            $leavelApplication->deleted_by = $loginUser?->id;
            $leavelApplication->deleted_at = Helper::trait_current_date();
            // return $leavelApplication;
            $leavelApplication->save();


            return $this->sendResponse($leavelApplication, "Your Leave Application Delete Successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * Leave List
     * company_id is nullable when token is exist
     */
    public function leave_list(Request $request)
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
                'fromdate_time' => ['nullable', 'date', 'date_format:Y-m-d'],
                'todate_time' => ['nullable', 'date', 'date_format:Y-m-d']
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            /* Pagination */
            $pagination = (isset($request?->page)) ? true : false;
            $perPage = (isset($request?->per_page)) ? $request?->per_page : env("API_PER_PAGE");
            $page = (isset($request?->page)) ? $request?->page : 1;

            $moduleDocuments = LeaveApplication::where('company_id', $company_id)->where('employee_id', $loginUser?->id);
            if ($request?->leave_type_id) {
                $moduleDocuments = $moduleDocuments->where('leave_type_id', $request?->leave_type_id);
            }
            if ($request?->fromdate_time && !$request?->todate_time) {
                $fromdateTime = Carbon::parse($request->fromdate_time)->toDateString();
                // $moduleDocuments = $moduleDocuments->whereDate('fromdate_time', "<=", Carbon::parse($request?->fromdate_time))->whereDate('todate_time', ">=", Carbon::parse($request?->fromdate_time));
                // $moduleDocuments = $moduleDocuments->where(function ($query) use ($request) {
                //     $fromdateTime = Carbon::parse($request->fromdate_time)->toDateString();
                //     $query->whereDate('fromdate_time', '<=', $fromdateTime)->whereDate('todate_time', '>=', $fromdateTime);
                // });
                $moduleDocuments = $moduleDocuments->whereDate('fromdate_time', '>=', $fromdateTime)->orWhereNull('todate_time');
            } else if ($request?->fromdate_time && $request?->todate_time) {
                $filter_start = Carbon::parse($request->fromdate_time)->toDateString();
                $filter_end = Carbon::parse($request->todate_time)->toDateString();

                // $moduleDocuments = $moduleDocuments->whereDate('fromdate_time', '>=', $filter_start);
                // $moduleDocuments = $moduleDocuments->whereDate('todate_time', '<=', $filter_end);

                $moduleDocuments = $moduleDocuments->where(function ($sub) use ($filter_start, $filter_end) {
                    $sub->whereDate('fromdate_time', '>=', $filter_start)
                        ->whereDate('fromdate_time', '<=', $filter_end);
                });
                $moduleDocuments = $moduleDocuments->where(function ($sub) use ($filter_start, $filter_end) {
                    $sub->whereDate('todate_time', '>=', $filter_start)
                        ->whereDate('todate_time', '<=', $filter_end)
                        ->orWhereNull('todate_time');
                });
                /*
                $moduleDocuments = $moduleDocuments->where(function ($query) use ($request) {
                    $filter_start = Carbon::parse($request->fromdate_time)->toDateString();
                    $filter_end = Carbon::parse($request->todate_time)->toDateString();
                    $query->where(function ($q) use ($filter_start) {
                        $q->whereDate('fromdate_time', '<=', $filter_start)->where(function ($sub) use ($filter_start) {
                            $sub->whereDate('todate_time', '>=', $filter_start)
                            ->orWhereNull('todate_time');
                        });
                    })
                    ->orWhere(function ($q) use ($filter_end) {
                        $q->whereDate('fromdate_time', '<=', $filter_end)
                        ->whereDate('todate_time', '>=', $filter_end);
                    });
                });
                */
            }
            // dd('leavelApplication 387', Helper::interpolateQuery($moduleDocuments->toSql(), $moduleDocuments->getBindings()), $moduleDocuments->first(), $loginUser->toArray());

            if ($request?->status) {
                $moduleDocuments = $moduleDocuments->where('status', $request?->status);
            }

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
                $tempResponseData['total'] = $moduleDocuments->count() . "";
                $tempResponseData['items'] = $moduleDocuments;
            }

            // $leavelApplication = $moduleDocuments->get();

            if (isset($tempResponseData['items'])) {
                $tempResponseData['items'] = $tempResponseData['items']->map(function ($record) {
                    $temp = [];
                    // $temp = $record;
                    $temp['id'] = $record?->id . "";
                    $temp['company_id'] = $record?->company_id . "";
                    $temp['employee_id'] = $record?->employee_id . "";
                    $temp['leave_type_id'] = $record?->leave_type_id . "";
                    $temp['leave_type_name'] = $record?->leave_type?->full_name . "";
                    $temp['leave_type_short_name'] = $record?->leave_type?->sort_name . "";
                    $temp['fromdate_time'] = ($record?->fromdate_time) ? \Carbon\Carbon::parse($record?->fromdate_time)->format('d M Y h:i A') : "";
                    $temp['todate_time'] = ($record?->todate_time) ? \Carbon\Carbon::parse($record?->todate_time)->format('d M Y h:i A') : "";
                    $temp['halfday_fullday'] = $record?->halfday_fullday . "";
                    $temp['singleday_multipleday'] = $record?->singleday_multipleday . "";
                    $temp['firsthalf_secondhalf'] = $record?->firsthalf_secondhalf . "";
                    $temp['leave_reason'] = $record?->leave_reason . "";
                    // $temp['attachment'] = ($record?->attachment && !empty($record?->attachment_url)) ?  : . "";
                    $temp['attachment_url'] = ($record?->attachment && !empty($record?->attachment_url)) ? $record?->attachment_url : "";
                    $temp['status'] = $record?->status . "";
                    $temp['reject'] = $record?->reject . "";
                    return $temp;
                });

                return $this->sendResponse($tempResponseData, "Your Leave Application List.");
            }
            return $this->sendError("Something want to wrong in data fetching.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendError("Something want to wrong in register API");
    }

    /**
     * Available Leave List for Dashboard
     */
    public function available_leave_list(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }
            $loginUser->load('employmentDetail');
            $company_id = $loginUser->company_id;

            $validator = Validator::make($request->all(), [
                'year' => ['nullable', 'integer'],
                'month' => ['nullable', 'integer', 'between:1,12'],
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $leaveTypes = LeaveType::where('company_id', $company_id)->where('status', 'active')->get();

            $currentMonth = (int) ($request->month ?? Carbon::now()->month);
            $currentYear = (int) ($request->year ?? Carbon::now()->year);
            if ($currentMonth >= 4) {
                $fyStart = Carbon::create($currentYear, 4, 1)->startOfDay();
                $fyEnd = Carbon::create($currentYear + 1, 3, 31)->endOfDay();
            } else {
                $fyStart = Carbon::create($currentYear - 1, 4, 1)->startOfDay();
                $fyEnd = Carbon::create($currentYear, 3, 31)->endOfDay();
            }

            $monthStartStr = Carbon::create($currentYear, $currentMonth, 1)->startOfDay()->toDateString();
            $monthEndStr = Carbon::create($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay()->toDateString();
            $isCurrentPeriod = ($currentYear === (int) Carbon::now()->year && $currentMonth === (int) Carbon::now()->month);

            $data = $leaveTypes->map(function ($type) use ($loginUser, $fyStart, $fyEnd, $currentMonth, $currentYear, $monthStartStr, $monthEndStr, $isCurrentPeriod) {
                $isCOff = (strtolower($type->sort_name) === 'c-off' || strtolower($type->sort_name) === 'coff' || strtolower($type->full_name) === 'compensatory off');

                if ($isCOff) {
                    $yearlyTotal = (float) $loginUser->getEarnedCoffCount(false, null, $fyStart->toDateString(), $fyEnd->toDateString());
                    $monthlyLimit = (float) $loginUser->getEarnedCoffCount(false, null, $monthStartStr, $monthEndStr);
                    $monthlyUsedCount = (float) $loginUser->getUsedCoffCount(false, null, $monthStartStr, $monthEndStr);
                    $monthlyAvailable = (float) $loginUser->getAvailableCoffCount(false, null, $monthStartStr, $monthEndStr);
                    $yearlyUsedCount = (float) $loginUser->getUsedCoffCount(false, null, $fyStart->toDateString(), $fyEnd->toDateString());
                } elseif ($type->carry_forward == 1) {
                    $yearlyTotal = (float) $type->count;
                    $monthlyLimit = (float) $loginUser->getAccruedLeaveCountForReport($type->id, $currentYear, $currentMonth);
                    $monthlyUsedCount = (float) $loginUser->getUsedLeaveCountForReport($type->id, $currentYear, $currentMonth);
                    $monthlyAvailable = $isCurrentPeriod
                        ? (float) $loginUser->getAvailableLeaveBalance($type->id)
                        : max(0.0, $monthlyLimit - $monthlyUsedCount);
                    $yearlyUsedCount = (float) $loginUser->getUsedLeaveCountForReport($type->id, $currentYear, $currentMonth);
                } else {
                    $yearlyTotal = (float) $type->count;
                    $monthlyLimit = (float) $loginUser->getAccruedLeaveCountForReport($type->id, $currentYear, $currentMonth);
                    $monthlyUsedCount = (float) $loginUser->getUsedLeaveCountForMonth($type->id, $currentYear, $currentMonth);
                    $monthlyAvailable = $isCurrentPeriod
                        ? (float) $loginUser->getAvailableLeaveBalance($type->id)
                        : max(0.0, $monthlyLimit - $monthlyUsedCount);
                    $yearlyUsedCount = (float) $loginUser->getUsedLeaveCountForReport($type->id, $currentYear, $currentMonth);
                }

                return [
                    'leave_type_id' => $type->id . "",
                    'leave_type_name' => $type->full_name . "",
                    'short_name' => $type->sort_name . "",
                    'carry_forward' => ($type->carry_forward ?? 0) . "",
                    'yearly_total' => $yearlyTotal . "",
                    'yearly_used' => $yearlyUsedCount . "",
                    'yearly_available' => max(0, $yearlyTotal - $yearlyUsedCount) . "",
                    'monthly_limit' => number_format($monthlyLimit, 2, '.', '') . "",
                    'total_accrued' => number_format($monthlyLimit, 2, '.', '') . "",
                    'monthly_used' => $monthlyUsedCount . "",
                    'monthly_available' => number_format($monthlyAvailable, 2, '.', '') . "",
                    'pending' => number_format($monthlyAvailable, 2, '.', '') . "",
                    'available_balance' => number_format($monthlyAvailable, 2, '.', '') . "",
                ];
            });

            return $this->sendResponse($data, "Available Leave List.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Leave Summary Report (same logic as web Leave Summary Report)
     */
    public function leave_summary_report(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $validator = Validator::make($request->all(), [
                'year' => ['required', 'integer'],
                'month' => ['required', 'integer', 'between:1,12'],
                'employee_id' => ['nullable', 'integer'],
            ]);
            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $year = (int) $request->year;
            $month = (int) $request->month;
            $employeeId = $request->employee_id ? (int) $request->employee_id : (int) $loginUser->id;

            $employee = \App\Models\Employee::with('employmentDetail')->find($employeeId);
            if (!$employee || (int) $employee->company_id !== (int) $loginUser->company_id) {
                return $this->sendError('Invalid employee.', [], [], 422);
            }

            $leaveTypes = LeaveType::where('company_id', $loginUser->company_id)->where('status', 'active')->get();
            $monthStartStr = Carbon::create($year, $month, 1)->startOfDay()->toDateString();
            $monthEndStr = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay()->toDateString();

            $leaveSummary = [];
            $grandTotalAssigned = 0;
            $grandTotalUsed = 0;
            $grandTotalPending = 0;

            foreach ($leaveTypes as $type) {
                $isCOff = (strtolower($type->sort_name) === 'c-off' || strtolower($type->sort_name) === 'coff' || strtolower($type->full_name) === 'compensatory off');

                if ($isCOff) {
                    $total = (float) $employee->getEarnedCoffCount(false, null, $monthStartStr, $monthEndStr);
                    $used = (float) $employee->getUsedCoffCount(false, null, $monthStartStr, $monthEndStr);
                    $pending = (float) $employee->getAvailableCoffCount(false, null, $monthStartStr, $monthEndStr);
                } elseif ($type->carry_forward == 1) {
                    $total = (float) $employee->getAccruedLeaveCountForReport($type->id, $year, $month);
                    $used = (float) $employee->getUsedLeaveCountForReport($type->id, $year, $month);
                    $pending = max(0.0, $total - $used);
                } else {
                    $total = (float) $employee->getAccruedLeaveCountForReport($type->id, $year, $month);
                    $used = (float) $employee->getUsedLeaveCountForMonth($type->id, $year, $month);
                    $pending = max(0.0, $total - $used);
                }

                $leaveSummary[] = [
                    'leave_type_id' => $type->id . "",
                    'leave_type_name' => $type->full_name . "",
                    'short_name' => $type->sort_name . "",
                    'carry_forward' => ($type->carry_forward ?? 0) . "",
                    'total' => $total . "",
                    'used' => $used > 0 ? $used . "" : "0",
                    'pending' => $pending > 0 ? $pending . "" : "0",
                ];

                if (strtolower($type->sort_name) === 'pl') {
                    $grandTotalAssigned += $total;
                    $grandTotalUsed += $used;
                    $grandTotalPending += $pending;
                }
            }

            return $this->sendResponse([
                'employee_id' => $employeeId . "",
                'employee_name' => $employee->full_name . "",
                'employee_code' => ($employee->employee_code ?? '') . "",
                'year' => $year . "",
                'month' => $month . "",
                'leave_summary' => $leaveSummary,
                'grand_total' => [
                    'total_leaves' => $grandTotalAssigned . "",
                    'used_leaves' => $grandTotalUsed > 0 ? $grandTotalUsed . "" : "0",
                    'pending_leaves' => $grandTotalPending > 0 ? $grandTotalPending . "" : "0",
                ],
            ], 'Leave Summary Report.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Helper to calculate leave days
     */
    private function calculateLeaveDays($leave)
    {
        if ($leave->halfday_fullday == 'halfday') {
            return 0.5;
        } else {
            if ($leave->singleday_multipleday == 'multipleday' && $leave->todate_time) {
                $from = Carbon::parse($leave->fromdate_time)->startOfDay();
                $to = Carbon::parse($leave->todate_time)->startOfDay();
                return $from->diffInDays($to) + 1;
            } else {
                return 1;
            }
        }
    }
}
