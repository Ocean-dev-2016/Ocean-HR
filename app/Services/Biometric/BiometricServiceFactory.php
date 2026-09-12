<?php

namespace App\Services\Biometric;

use App\Models\BiometricMachine;
use App\Services\Biometric\Contracts\BiometricProviderInterface;
use App\Services\Biometric\ETimeOfficeService;
use App\Services\Biometric\MintraService;
use App\Services\Biometric\OldCRMService;
use InvalidArgumentException;

class BiometricServiceFactory
{
    /**
     * Create a biometric service instance based on provider type
     *
     * @param string $providerType Provider type: 'minop', 'etimeoffice', 'mintra'
     * @param BiometricMachine|null $machine Optional machine instance
     * @return BiometricProviderInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $providerType, ?BiometricMachine $machine = null): BiometricProviderInterface
    {
        switch ($providerType) {
            case 'etimeoffice':
                $service = new ETimeOfficeService();
                break;

            case 'mintra':
                $service = new MintraService();
                break;

            case 'old_crm':
                $service = new OldCRMService();
                break;

            case 'minop':
                // Minop is push-based, doesn't need a service for fetching
                // It receives data via webhooks (existing MinopAttendanceController handles this)
                throw new InvalidArgumentException("Minop is a push-based provider. No service needed for data fetching.");

            default:
                throw new InvalidArgumentException("Unknown provider type: {$providerType}");
        }

        if ($machine) {
            $service->setMachine($machine);
        }

        return $service;
    }

    /**
     * Check if provider type is pull-based
     *
     * @param string $providerType
     * @return bool
     */
    public static function isPullBased(string $providerType): bool
    {
        return in_array($providerType, ['etimeoffice', 'mintra', 'old_crm']);
    }

    /**
     * Check if provider type is push-based
     *
     * @param string $providerType
     * @return bool
     */
    public static function isPushBased(string $providerType): bool
    {
        return $providerType === 'minop';
    }
}
