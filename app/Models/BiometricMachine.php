<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class BiometricMachine extends Model
{
    use SoftDeletes;

    protected $table = 'biometric_machines';

    protected $fillable = [
        'company_id',
        'machine_name',
        'serial_number',
        'ip_address',
        'port',
        'provider_type',
        'api_url',
        'auth_type',
        'corporate_id',
        'api_username',
        'api_password',
        'bearer_token',
        'api_key_name',
        'api_key_value',
        'custom_headers',
        'status',
        'is_active',
        'last_sync_at',
        'last_sync_status',
        'last_sync_error',
        'sync_interval_minutes',
        'description',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'sync_interval_minutes' => 'integer',
    ];

    protected $hidden = [
        'api_password', // Hide encrypted password from JSON
        'bearer_token', // Hide bearer token from JSON
        'api_key_value', // Hide API key from JSON
    ];

    /**
     * Encrypt API password when setting
     */
    public function setApiPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['api_password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt API password when getting
     */
    public function getApiPasswordAttribute($value)
    {
        if (!empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get raw encrypted password (for comparison/update without re-encryption)
     */
    public function getRawApiPasswordAttribute()
    {
        return $this->attributes['api_password'] ?? null;
    }

    /**
     * Encrypt API key value when setting
     */
    public function setApiKeyValueAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['api_key_value'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt API key value when getting
     */
    public function getApiKeyValueAttribute($value)
    {
        if (!empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get raw encrypted API key value
     */
    public function getRawApiKeyValueAttribute()
    {
        return $this->attributes['api_key_value'] ?? null;
    }

    /**
     * Get custom headers as array
     */
    public function getCustomHeadersAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        try {
            return json_decode($value, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set custom headers as JSON
     */
    public function setCustomHeadersAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['custom_headers'] = json_encode($value);
        } else {
            $this->attributes['custom_headers'] = $value;
        }
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * Scope: Get only pull-based providers (eTimeOffice, Mintra)
     */
    public function scopePullBased($query)
    {
        return $query->whereIn('provider_type', ['etimeoffice', 'mintra', 'old_crm']);
    }

    /**
     * Scope: Get only push-based providers (Minop)
     */
    public function scopePushBased($query)
    {
        return $query->where('provider_type', 'minop');
    }

    /**
     * Scope: Get active machines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get machines by provider type
     */
    public function scopeByProvider($query, $providerType)
    {
        return $query->where('provider_type', $providerType);
    }

    /**
     * Scope: Get machines for company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Check if provider is pull-based (requires API sync)
     */
    public function isPullBasedProvider(): bool
    {
        return in_array($this->provider_type, ['etimeoffice', 'mintra', 'old_crm']);
    }

    /**
     * Check if provider is push-based (uses webhooks)
     */
    public function isPushBasedProvider(): bool
    {
        return $this->provider_type === 'minop';
    }

    /**
     * Check if sync button should be shown
     */
    public function shouldShowSyncButton(): bool
    {
        if (!$this->isPullBasedProvider() || !$this->is_active || empty($this->api_url)) {
            return false;
        }

        $authType = $this->auth_type ?? 'basic';

        // Check credentials based on auth type
        if ($authType === 'basic') {
            return !empty($this->api_username);
            // Password is optional
        } elseif ($authType === 'bearer_token') {
            return !empty($this->bearer_token);
        } elseif ($authType === 'api_key') {
            return !empty($this->api_key_name) && !empty($this->getRawApiKeyValueAttribute());
        } elseif ($authType === 'custom') {
            $customHeaders = $this->custom_headers ?? [];
            return !empty($customHeaders);
        }

        return false;
    }

    /**
     * Check if sync can be performed now
     */
    public function canSyncNow(): bool
    {
        return $this->shouldShowSyncButton();
    }

    /**
     * Check if machine has required credentials for the selected auth type
     */
    public function hasRequiredCredentials(): bool
    {
        if (empty($this->api_url)) {
            return false;
        }

        $authType = $this->auth_type ?? 'basic';

        if ($authType === 'basic') {
            return !empty($this->api_username);
        } elseif ($authType === 'bearer_token') {
            return !empty($this->bearer_token);
        } elseif ($authType === 'api_key') {
            return !empty($this->api_key_name) && !empty($this->getRawApiKeyValueAttribute());
        } elseif ($authType === 'custom') {
            $customHeaders = $this->custom_headers ?? [];
            return !empty($customHeaders);
        }

        return false;
    }

    /**
     * Get last sync status
     */
    public function getLastSyncStatus(): ?string
    {
        return $this->last_sync_status;
    }

    /**
     * Get human-readable time since last sync
     */
    public function getLastSyncTimeAgo(): string
    {
        if (!$this->last_sync_at) {
            return 'Never synced';
        }

        return $this->last_sync_at->diffForHumans();
    }

    /**
     * Check if machine should sync based on interval
     */
    public function shouldSync(): bool
    {
        if (!$this->is_active || !$this->isPullBasedProvider()) {
            return false;
        }

        if (!$this->last_sync_at) {
            return true; // Never synced, should sync
        }

        $intervalMinutes = $this->sync_interval_minutes ?? 60;
        $nextSyncTime = $this->last_sync_at->copy()->addMinutes($intervalMinutes);

        return now()->greaterThanOrEqualTo($nextSyncTime);
    }
}
