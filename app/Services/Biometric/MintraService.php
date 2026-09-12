<?php

namespace App\Services\Biometric;

use App\Models\BiometricMachine;
use App\Services\Biometric\Contracts\BiometricProviderInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MintraService implements BiometricProviderInterface
{
    protected ?BiometricMachine $machine = null;

    /**
     * Get the provider name
     */
    public function getProviderName(): string
    {
        return 'Mintra';
    }

    /**
     * Get the integration type (push or pull)
     */
    public function getIntegrationType(): string
    {
        return 'pull';
    }

    /**
     * Set the biometric machine instance
     */
    public function setMachine(BiometricMachine $machine): self
    {
        $this->machine = $machine;
        return $this;
    }

    /**
     * Get API base URL
     */
    protected function getApiUrl(): string
    {
        return $this->machine->api_url ?? '';
    }

    /**
     * Authenticate with Mintra API
     * 
     * TODO: Update this method once Mintra API documentation is provided
     */
    public function authenticate(): array
    {
        if (!$this->machine) {
            throw new Exception('Biometric machine not set');
        }

        // TODO: Implement actual authentication based on API documentation
        $response = Http::post($this->getApiUrl() . '/auth/login', [
            'username' => $this->machine->api_username,
            'password' => $this->machine->api_password,
        ]);

        if (!$response->successful()) {
            throw new Exception('Authentication failed: ' . $response->body());
        }

        $data = $response->json();
        
        // TODO: Extract token/session from response based on actual API response format
        return [
            'token' => $data['token'] ?? null,
            'session' => $data['session'] ?? null,
        ];
    }

    /**
     * Test connection to Mintra API
     */
    public function testConnection(): array
    {
        try {
            $this->authenticate();
            return [
                'success' => true,
                'message' => 'Connection successful',
            ];
        } catch (Exception $e) {
            Log::error('Mintra connection test failed', [
                'machine_id' => $this->machine->id ?? null,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch attendance records from Mintra API
     * 
     * TODO: Update this method once Mintra API documentation is provided
     */
    public function fetchAttendance(Carbon $startDate, Carbon $endDate): array
    {
        if (!$this->machine) {
            throw new Exception('Biometric machine not set');
        }

        // Authenticate first
        $auth = $this->authenticate();
        $token = $auth['token'] ?? null;

        // TODO: Implement actual attendance fetch based on API documentation
        $response = Http::withToken($token)
            ->get($this->getApiUrl() . '/attendance', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);

        if (!$response->successful()) {
            throw new Exception('Failed to fetch attendance: ' . $response->body());
        }

        $data = $response->json();
        
        // TODO: Transform API response to standard format
        return $data['data'] ?? $data ?? [];
    }

    /**
     * Sync employees from Mintra API (optional)
     * 
     * TODO: Implement if needed based on API documentation
     */
    public function syncEmployees(): array
    {
        // TODO: Implement employee sync if required
        throw new Exception('Employee sync not implemented yet');
    }

    /**
     * Push salary data to Mintra API (not supported)
     */
    public function pushSalaryData(array $salaryData): array
    {
        return [
            'success' => true,
            'message' => 'Salary push not supported for Mintra',
        ];
    }
}
