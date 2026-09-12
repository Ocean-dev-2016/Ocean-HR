<?php

namespace App\Http\Requests;

use App\Models\AssetsAllocationMaster;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAsignAssets;
use App\Models\EmployeeType;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeAsignAssetsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */


    public function rules(Request $request): array
    {
        // Try to get the ID from route model binding or from request input
        $employee_asign_assets = $request->route('employee_asign_assets');

        $id = 0; // default for create
        if ($employee_asign_assets instanceof EmployeeAsignAssets) {
            $id = $employee_asign_assets->id;
        } elseif ($request->has('edit_id')) {
            $id = $request->input('edit_id');
        }

        return [
            'company_id' => ['required', Rule::exists((new Company())->getTable(), 'id')],
            'employee_id' => ['required', Rule::exists((new Employee())->getTable(), 'id')],
            'assets_id' => ['required', Rule::exists((new AssetsAllocationMaster())->getTable(), 'id')],
            'date' => [
                'required',
                Rule::unique((new EmployeeAsignAssets())->getTable())
                    ->where(fn($query) => $query->where('company_id', $request->company_id))
                    ->ignore($id), // ignore current record if updating
            ],
            'reference_no' => ['required'],
            'descrption' => ['required'],
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,doc,docx,gif|max:10240',
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
