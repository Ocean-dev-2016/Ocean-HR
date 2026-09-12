<?php

namespace App\Http\Requests;

use App\Helpers\Helper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Company;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompanyRequest extends FormRequest
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
        // dd($request->all());
        $id = $request->route('company') ?? 0;
        $companyId = is_object($id) ? $id->id : $id;
        $isEdit = $companyId ? true : false;
        $dateFormats = array_keys(Helper::getSupportedDateFormats());
        $timeFormats = array_keys(Helper::getSupportedTimeFormats());
        return [
            'platform' => ['nullable', 'max:255'],
            'api_key' => ['nullable', 'max:255'],
            'gst_no' => [
                'nullable',
                'size:15', // GSTIN must be exactly 15 characters
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                Rule::unique((new Company())->getTable())->ignore($companyId),
            ],
            'company_name' => [
                'required',
                'max:255',
                Rule::unique((new Company())->getTable())->ignore($companyId),
            ],
            'person_name' => [
                'required',
                'max:255'
            ],
            'whatsapp_number' => [
                'required',
                'numeric',
                'regex:/^\d{10}$/',
                Rule::unique((new Company())->getTable(), 'whatsapp_number')->ignore($companyId),
            ],
            'email' => [
                'required',
                'email',
                Rule::unique((new Company())->getTable(), 'email')->ignore($companyId),
            ],
            'password' => array_merge(
                $isEdit ? ['nullable'] : ['required'],
                ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/']
            ),
            'country_id' => $isEdit ? ['nullable'] : ['required'],
            'state_id' => $isEdit ? ['nullable'] : ['required'],
            'city_id' => $isEdit ? ['nullable'] : ['required'],
            'plan_id' => $isEdit ? ['nullable'] : ['required'],
            'otp' => $isEdit ? ['nullable'] : ['required'],
            'company_logo' => ['mimes:jpeg,png,jpg,webp|max:2048'],
            'white_labeling_logo' => ['mimes:jpeg,png,jpg,webp|max:2048'],
            'company_favicon' => ['mimes:jpeg,png,jpg,webp|max:2048'],
            'app_logo' => ['mimes:jpeg,png,jpg,webp|max:2048'],
            'header_image' => ['mimes:jpeg,png,jpg,webp|max:2048'],
            'footer_image' => ['mimes:jpeg,png,jpg,webp|max:2048'],
            'hra_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employee_code_auto_generation' => ['nullable', Rule::in(array_keys(config('constants.employee_code_auto_generation')))],
            'date_format' => ['nullable', Rule::in($dateFormats)],
            'time_format' => ['nullable', Rule::in($timeFormats)],
            'host' => ['nullable', 'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'port' => 'nullable|numeric',
            'username' => ['nullable', 'regex:/^[a-zA-Z0-9]+\.[a-zA-Z0-9]+@[a-zA-Z0-9.-]+\.com$/'],
            //'password' => 'nullable|string',
            'encryption' => 'nullable|string|in:ssl,tls',
            'from_address' => ['nullable', 'regex:/^[a-zA-Z0-9]+\.[a-zA-Z0-9]+@[a-zA-Z0-9.-]+\.com$/'],
            'from_name' => 'nullable|string',
            'bcc' => ['nullable', 'string', 'regex:/^([^,]+@[^,]+\.[^,]+)(,\s*[^,]+@[^,]+\.[^,]+)*$/'],
        ];
    }

    public function messages()
    {
        return [
            'gst_no.required' => 'The GST number is required.',
            'gst_no.regex' => 'The GST number is invalid.',
            'company_name.required' => 'The company name is required.',
            'company_name.unique' => 'This company name is already registered.',
            'person_name.required' => 'The person name is required.',
            'whatsapp_number.required' => 'The whatsapp number is required.',
            'whatsapp_number.unique' => 'This WhatsApp number is already registered with another company.',
            'email.required' => 'The email is required.',
            'email.unique' => 'This email address is already registered with another company.',
            'password.required' => 'The password is required.',
            'country_id.required' => 'The country is required.',
            'state_id.required' => 'The state is required.',
            'city_id.required' => 'The city is required.',
            'plan_id.required' => 'The plan is required.',
            'employee_code_auto_generation.required' => 'The employee code auto generation is required.',
            'employee_code_auto_generation.in' => 'The employee code auto generation must be either auto or manual.',
            'host.regex' => 'The host must be a valid domain, such as smtp.example.com.',
            'port.numeric' => 'The port must be a number.',
            'username.regex' => 'The username must be a valid domain or subdomain.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'encryption.in' => 'The encryption must be either ssl or tls.',
            'from_address.regex' => 'The from address must be a valid domain, like mail.example.com.',
            'from_name.string' => 'The from name must be a valid string.',
            'bcc.regex' => 'The BCC field must contain a valid email address or a comma-separated list of valid email addresses.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            // Return JSON response for API or AJAX requests
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422));
        }

        // Default behavior (redirect back with errors)
        parent::failedValidation($validator);
    }
}
