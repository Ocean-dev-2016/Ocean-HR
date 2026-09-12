<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\ReferenceMaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class ReferenceMasterRequest extends FormRequest
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
        $id = $request->route('reference_master') ?? 0;
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
                Rule::unique((new ReferenceMaster())->getTable())
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id)
                            ->whereNull('deleted_at');
                    })->ignore($id),
            ],

            'display_order' => [
                'nullable',
                Rule::unique((new ReferenceMaster())->getTable(), 'display_order') // specify column
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id)
                            ->where('type', $request->type)
                            ->whereNull('deleted_at'); // optional if soft delete
                    })
                    ->ignore($id, 'id'), // explicitly ignore current row by id
            ],



            'status' => 'required|in:active,inactive',
        ];
        // dd("L-47", $rules, $id, $request->all(), $request->route('leave_type'), $request->route()->parameters());
        return $rules;
    }
}
