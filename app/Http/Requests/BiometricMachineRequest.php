<?php

namespace App\Http\Requests;

use App\Models\Company;
use App\Models\BiometricMachine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BiometricMachineRequest extends FormRequest
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
    public function rules(): array
    {
        $id = $this->route('biometric-machine') ?? 0;
        $providerType = $this->input('provider_type', 'minop');
        $isPullBased = in_array($providerType, ['etimeoffice', 'mintra', 'old_crm']);
        
        $rules = [
            'company_id' => [
                'required',
                Rule::exists((new Company())->getTable(), 'id')
            ],
            'provider_type' => [
                'required',
                'in:minop,etimeoffice,mintra,old_crm',
            ],
            'ip_address' => [
                'required',
                'ip',
                Rule::unique((new BiometricMachine())->getTable())->where(function ($query) {
                    return $query->where('company_id', $this->company_id)
                                 ->where('port', $this->port);
                })->ignore($id),
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
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            // API credentials - required for pull-based providers
            'api_url' => [
                $isPullBased ? 'required' : 'nullable',
                'url',
                'max:500',
            ],
            'auth_type' => [
                $isPullBased ? 'required' : 'nullable',
                'in:basic,bearer_token,api_key,custom',
            ],
            'corporate_id' => [
                ($providerType === 'etimeoffice' && $this->input('auth_type') === 'basic') ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'api_username' => [
                ($isPullBased && $this->input('auth_type') === 'basic') ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'api_password' => [
                'nullable', // Always optional - can be set later
                'string',
                'max:500',
            ],
            'bearer_token' => [
                ($isPullBased && $this->input('auth_type') === 'bearer_token') ? 'required' : 'nullable',
                'string',
                'max:1000',
            ],
            'api_key_name' => [
                ($isPullBased && $this->input('auth_type') === 'api_key') ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'api_key_value' => [
                ($isPullBased && $this->input('auth_type') === 'api_key') ? 'required' : 'nullable',
                'string',
                'max:1000',
            ],
            'custom_headers' => [
                ($isPullBased && $this->input('auth_type') === 'custom') ? 'required' : 'nullable',
                'string',
            ],
            'sync_interval_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440', // Max 24 hours
            ],
        ];

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $providerType = $this->input('provider_type', 'minop');
            $isPullBased = in_array($providerType, ['etimeoffice', 'mintra', 'old_crm']);
            
            // For pull-based providers, validate based on auth type
            if ($isPullBased) {
                $apiUrl = $this->input('api_url');
                $authType = $this->input('auth_type', 'basic');
                
                if (empty($apiUrl)) {
                    $validator->errors()->add('api_url', 'API URL is required for pull-based providers.');
                }
                
                // Validate based on auth type
                if ($authType === 'basic') {
                    $apiUsername = $this->input('api_username');
                    // Password is optional - can be set later
                    
                    if (empty($apiUsername)) {
                        $validator->errors()->add('api_username', 'API Username is required for Basic Auth.');
                    }
                    
                    // For eTimeOffice with Basic Auth, corporate_id is required
                    if ($providerType === 'etimeoffice') {
                        $corporateId = $this->input('corporate_id');
                        if (empty($corporateId)) {
                            $validator->errors()->add('corporate_id', 'Corporate ID is required for eTimeOffice provider with Basic Auth.');
                        }
                    }
                } elseif ($authType === 'bearer_token') {
                    $bearerToken = $this->input('bearer_token');
                    if (empty($bearerToken)) {
                        $validator->errors()->add('bearer_token', 'Bearer Token is required for Bearer Token authentication.');
                    }
                } elseif ($authType === 'api_key') {
                    $apiKeyName = $this->input('api_key_name');
                    $apiKeyValue = $this->input('api_key_value');
                    
                    if (empty($apiKeyName)) {
                        $validator->errors()->add('api_key_name', 'API Key Name is required for API Key authentication.');
                    }
                    if (empty($apiKeyValue)) {
                        $validator->errors()->add('api_key_value', 'API Key Value is required for API Key authentication.');
                    }
                } elseif ($authType === 'custom') {
                    $customHeaders = $this->input('custom_headers');
                    if (empty($customHeaders)) {
                        $validator->errors()->add('custom_headers', 'Custom Headers are required for Custom authentication.');
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'Selected company does not exist.',
            'provider_type.required' => 'Provider type is required.',
            'provider_type.in' => 'Provider type must be one of: minop, etimeoffice, mintra, old_crm.',
            'ip_address.required' => 'IP address is required.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'ip_address.unique' => 'A biometric machine with this IP address and port already exists for this company.',
            'port.required' => 'Port is required.',
            'port.numeric' => 'Port must be a number.',
            'port.min' => 'Port must be at least 1.',
            'port.max' => 'Port must not exceed 65535.',
            'machine_name.max' => 'Machine name must not exceed 255 characters.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
            'api_url.required' => 'API URL is required for pull-based providers.',
            'api_url.url' => 'Please enter a valid API URL.',
            'auth_type.required' => 'Authentication Type is required for pull-based providers.',
            'auth_type.in' => 'Authentication Type must be one of: basic, bearer_token, api_key, custom.',
            'corporate_id.required' => 'Corporate ID is required for eTimeOffice provider with Basic Auth.',
            'api_username.required' => 'API Username is required for Basic Auth.',
            'bearer_token.required' => 'Bearer Token is required for Bearer Token authentication.',
            'api_key_name.required' => 'API Key Name is required for API Key authentication.',
            'api_key_value.required' => 'API Key Value is required for API Key authentication.',
            'custom_headers.required' => 'Custom Headers are required for Custom authentication.',
            'sync_interval_minutes.integer' => 'Sync interval must be a number.',
            'sync_interval_minutes.min' => 'Sync interval must be at least 1 minute.',
            'sync_interval_minutes.max' => 'Sync interval must not exceed 1440 minutes (24 hours).',
        ];
    }
}
