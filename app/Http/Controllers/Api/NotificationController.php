<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Notification List for Employee
     */
    public function notification_list(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $notifications = Notification::where('user_id', $loginUser->id)
                ->where('user_type', 'Team')
                ->where('module_name', 'Leave')
                ->where('status', 'active')
                ->orderBy('id', 'DESC');

            if ($request->has('limit')) {
                $notifications = $notifications->limit($request->limit);
            }

            $notifications = $notifications->get();

            $data = $notifications->map(function ($item) {
                return [
                    'id' => $item->id . "",
                    'title' => $item->title . "",
                    'body' => $item->body . "",
                    'message' => $item->body . "",
                    'module_name' => $item->module_name . "",
                    'module_id' => $item->module_id . "",
                    'module_action' => $item->module_action . "",
                    'notify_read' => $item->notify_read . "",
                    'date' => Carbon::parse($item->created_at)->format('d-m-Y H:i:s'),
                ];
            });

            return $this->sendResponse($data, "Notification List.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Mark Notification as Read
     */
    public function mark_as_read(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            if ($request->id) {
                Notification::where('id', $request->id)
                    ->where('user_id', $loginUser->id)
                    ->update(['notify_read' => 1]);
            } else {
                Notification::where('user_id', $loginUser->id)
                    ->update(['notify_read' => 1]);
            }

            return $this->sendResponse([], "Notification marked as read successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Expense Notification List
     */
    public function notification_expense_list(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $notifications = Notification::where('user_id', $loginUser->id)
                ->where('user_type', 'Team')
                ->where('module_name', 'Expense')
                ->where('status', 'active')
                ->orderBy('id', 'DESC');

            if ($request->has('limit')) {
                $notifications = $notifications->limit($request->limit);
            }

            $notifications = $notifications->get();

            $data = $notifications->map(function ($item) {
                return [
                    'id' => $item->id . "",
                    'title' => $item->title . "",
                    'body' => $item->body . "",
                    'message' => $item->body . "",
                    'module_name' => $item->module_name . "",
                    'module_id' => $item->module_id . "",
                    'module_action' => $item->module_action . "",
                    'notify_read' => $item->notify_read . "",
                    'date' => Carbon::parse($item->created_at)->format('d-m-Y H:i:s'),
                ];
            });

            return $this->sendResponse($data, "Expense Notification List.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Update Device Token for Push Notifications
     */
    public function update_device_token(Request $request)
    {
        try {
            $loginUser = Auth::user();
            if (!$loginUser) {
                return $this->sendError("Unauthorization", [], [], 401);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'device_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError($validator->messages()->first(), $validator->messages(), [], 422);
            }

            $loginUser->device_token = $request->device_token;
            $loginUser->save();

            return $this->sendResponse([], "Device token updated successfully.");
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}

