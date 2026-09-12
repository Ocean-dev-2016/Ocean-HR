# Postman Collection for 3rd Party Attendance API

## Import Instructions

### Option 1: Import from File
1. Open Postman
2. Click **Import** button (top left)
3. Select **File** tab
4. Choose the file: `3rd-party-attendance-api.postman_collection.json`
5. Click **Import**

### Option 2: Import from Link
1. Open Postman
2. Click **Import** button
3. Select **Link** tab
4. Paste this link (if hosted) or use the file path

## Setup Environment Variables

After importing, set up these environment variables:

1. **base_url**: Your API base URL
   - Local: `http://localhost:8000`
   - Production: `https://yourdomain.com`

2. **app_key**: Your company's app_key from the database
   - Get from `companies` table, `app_key` column

3. **company_id**: Your company ID (optional, alternative to app_key)
   - Get from `companies` table, `id` column

## API Endpoint

**URL**: `POST /webhook/3rd-party/attendance`

**Base URL Examples**:
- Local: `http://localhost:8000/webhook/3rd-party/attendance`
- Production: `https://yourdomain.com/webhook/3rd-party/attendance`

## Request Examples

### Single Record
```json
{
    "app_key": "your_app_key",
    "biomax_id": "12345",
    "timestamp": "2025-12-19 10:30:00",
    "type": "in",
    "txnId": "TXN001"
}
```

### Multiple Records
```json
{
    "app_key": "your_app_key",
    "records": [
        {
            "biomax_id": "12345",
            "timestamp": "2025-12-19 09:00:00",
            "type": "in",
            "txnId": "TXN001"
        },
        {
            "biomax_id": "12345",
            "timestamp": "2025-12-19 18:00:00",
            "type": "out",
            "txnId": "TXN002"
        }
    ]
}
```

## Field Mappings

### Required Fields
- `biomax_id` or `biometric_user_id` - Employee's biometric ID
- `timestamp` or `datetime` or `time` - Attendance timestamp

### Optional Fields
- `app_key` - Company app key (can be in header as X-API-KEY)
- `company_id` - Company ID (alternative to app_key)
- `type` - "in" or "out" (auto-detected if not provided)
- `txnId` or `transaction_id` - Unique transaction ID
- `device_serial` or `serial_number` - Device serial number
- `device_ip` or `ip_address` - Device IP address
- `remark` or `notes` - Additional notes

## Response Format

### Success Response
```json
{
    "status": true,
    "processed": 2,
    "created": 2,
    "skipped": 0,
    "errors": [],
    "transStatus": [
        {
            "txnId": "TXN001",
            "biomax_id": "12345",
            "status": 1,
            "message": "Record created successfully"
        }
    ]
}
```

### Error Response
```json
{
    "status": false,
    "message": "Error message here",
    "data": [],
    "errors": []
}
```

## Rate Limiting

- Default: **60 requests per minute** per company
- Configurable in `company_details.attendance_request_rate_limit_per_minutes`
- Returns HTTP 429 when limit exceeded

## Testing Checklist

- [ ] Test single record submission
- [ ] Test multiple records submission
- [ ] Test with app_key in body
- [ ] Test with app_key in header (X-API-KEY)
- [ ] Test with company_id
- [ ] Test auto IN/OUT detection
- [ ] Test duplicate record handling
- [ ] Test rate limiting (send 61+ requests in 1 minute)
- [ ] Test invalid biomax_id
- [ ] Test invalid timestamp format
- [ ] Test missing required fields

## Notes

1. The API uses `biomax_id` (which maps to `biometric_user_id` in employees table)
2. If `biomax_id` is not found, it falls back to `employee_code`
3. Records are stored individually (one by one)
4. Duplicate records are detected by `txn_id` or employee+date+time+type
5. Rate limiting is per company, per minute
