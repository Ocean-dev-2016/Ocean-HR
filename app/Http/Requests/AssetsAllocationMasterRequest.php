<?php

namespace App\Http\Requests;

use App\Models\AssetsAllocationMaster;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class AssetsAllocationMasterRequest extends FormRequest
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
        $id = $request->route('assets_allocation_master') ?? 0;
        //  dd($id);
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique((new AssetsAllocationMaster())->getTable())
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id)
                            ->whereNull('deleted_at');
                    })->ignore($id),
            ],
            'display_order' => [
                'nullable',
                Rule::unique((new AssetsAllocationMaster())->getTable())
                    ->where(function ($query) use ($request, $id) {
                        return $query->where('company_id', $request->company_id)
                            ->where('name', $request->name)
                            ->where('id', '!=', $id);
                    }),
            ],


            'status' => 'required|in:active,inactive',
        ];
        // dd("L-47", $rules, $id, $request->all(), $request->route('leave_type'), $request->route()->parameters());
        return $rules;
    }
}
