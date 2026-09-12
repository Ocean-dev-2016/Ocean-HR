<?php

namespace App\Http\Requests;

use App\Models\Bonus;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class BonusRequest extends FormRequest
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
        $bonus = $request->route('bonus');

        $id = $bonus instanceof Bonus
            ? $bonus->id
            : ($request->edit_id ?? 0);
        // dd($id);
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'year' => 'required',
            'month' => 'required',
            'amount' => 'required',
            'branch' => 'nullable',

            'employee' => [
                'required',
                Rule::unique((new Bonus())->getTable(), 'employee')
                    ->where(function ($query) use ($request) {
                        return $query->where('company_id', $request->company_id)
                            ->where('month', $request->month)
                            ->where('year', $request->year)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($id, 'id'),
            ],


            'status' => 'required|in:active,inactive',
        ];




        // dd("L-47", $rules, $id, $request->all(), $request->route('leave_type'), $request->route()->parameters());
        return $rules;
    }
}
