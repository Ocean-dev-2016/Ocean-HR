<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Company;
use App\Models\LoanTypes;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class LoanTypesRequest extends FormRequest
{

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

        $id = $request->route('loan_types') ?? 0;
        if($id == 0 && $request?->edit_id){
            $id = $request?->edit_id;
        }
        //  dd($id);
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'name' => [
                'required',
                'max:255',
                Rule::unique((new LoanTypes())->getTable())->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })->ignore($id),
            ],
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
        // dd("L-44", $rules, $id, $request->all(), $request->route('leave_type'), $request->route()->parameters());
        return $rules;
    }
}
