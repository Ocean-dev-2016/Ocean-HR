<?php

namespace App\Services\Biometric\Contracts;

use App\Models\BiometricMachine;
use Carbon\Carbon;

interface BiometricProviderInterface
{
    /**
     * Get the provider name
     *
     * @return string
     */
    public function getProviderName(): string;

    /**
     * Get the integration type (push or pull)
     *
     * @return string 'push' or 'pull'
     */
    public function getIntegrationType(): string;

    /**
     * Authenticate with the provider API
     *
     * @return array Contains authentication data (token, etc.)
     * @throws \Exception If authentication fails
     */
    public function authenticate(): array;

    /**
     * Test connection to the provider API
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function testConnection(): array;

    /**
     * Fetch attendance records from the provider
     *
     * @param Carbon $startDate Start date for attendance records
     * @param Carbon $endDate End date for attendance records
     * @return array Array of attendance records
     * @throws \Exception If fetch fails
     */
    public function fetchAttendance(Carbon $startDate, Carbon $endDate): array;

    /**
     * Sync employees from the provider (optional)
     *
     * @return array Array of employee records
     * @throws \Exception If sync fails
     */
    public function syncEmployees(): array;

    /**
     * Set the biometric machine instance
     *
     * @param BiometricMachine $machine
     * @return self
     */
    public function setMachine(BiometricMachine $machine): self;

    /**
     * Push salary data to the provider
     *
     * @param array $salaryData
     * @return array
     */
    public function pushSalaryData(array $salaryData): array;
}
