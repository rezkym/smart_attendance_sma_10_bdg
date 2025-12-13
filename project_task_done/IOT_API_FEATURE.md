# IoT API Management - Feature Specification

## Overview
Fitur ini menambahkan pengelolaan lengkap untuk koneksi ESP32/IoT devices ke sistem absensi, termasuk konfigurasi device, API key management, dan logging semua request yang masuk.

---

## 📋 Feature Breakdown

### Fase 1: Backend - IoT Device Management
- [ ] Migration: `iot_devices` table
- [ ] Migration: `iot_logs` table
- [ ] Model: `IotDevice` dengan relationships
- [ ] Model: `IotLog` dengan relationships
- [ ] Enum: `IotLogType` (REQUEST, RESPONSE, ERROR)
- [ ] Enum: `IotDeviceStatus` (ACTIVE, INACTIVE, MAINTENANCE)
- [ ] Repository: `IotDeviceRepository` + Interface
- [ ] Repository: `IotLogRepository` + Interface
- [ ] Service: `IotDeviceService`
- [ ] Service: `IotLogService`

### Fase 2: Backend - API Authentication & Logging
- [ ] Middleware: `AuthenticateIotDevice` (API Key validation)
- [ ] Update: `AttendanceController@store` to log requests
- [ ] Form Request: `StoreIotDeviceRequest`
- [ ] Form Request: `UpdateIotDeviceRequest`
- [ ] Controller: `IotDeviceController` (Admin CRUD)
- [ ] Controller: `IotLogController` (Admin View)
- [ ] Policy: `IotDevicePolicy`
- [ ] Routes: API routes with middleware
- [ ] Routes: Admin panel routes

### Fase 3: Frontend - Admin Panel
- [ ] View: `iot-devices/index.blade.php` (DataTable)
- [ ] View: `iot-logs/index.blade.php` (DataTable with filters)
- [ ] JS: `iot-devices.js`
- [ ] JS: `iot-logs.js`
- [ ] Menu: Add to sidebar config
- [ ] Permission: Add IoT management permissions

---

## 🗄️ Database Schema

### Table: `iot_devices`
```sql
CREATE TABLE iot_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Device name (e.g., "ESP32 Ruang 101")',
    device_code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique device identifier',
    api_key VARCHAR(64) NOT NULL UNIQUE COMMENT 'API key for authentication',
    description TEXT NULL,
    location VARCHAR(255) NULL COMMENT 'Physical location of device',
    classroom_id BIGINT UNSIGNED NULL COMMENT 'Linked classroom (optional)',
    status ENUM('active', 'inactive', 'maintenance') NOT NULL DEFAULT 'active',
    last_seen_at TIMESTAMP NULL COMMENT 'Last successful API call',
    ip_address VARCHAR(45) NULL COMMENT 'Last known IP address',
    firmware_version VARCHAR(20) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE SET NULL,
    INDEX idx_api_key (api_key),
    INDEX idx_device_code (device_code),
    INDEX idx_status (status)
);
```

### Table: `iot_logs`
```sql
CREATE TABLE iot_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    iot_device_id BIGINT UNSIGNED NOT NULL,
    log_type ENUM('request', 'response', 'error') NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL COMMENT 'HTTP method: GET, POST, etc.',
    request_payload JSON NULL COMMENT 'Request body/params',
    response_payload JSON NULL COMMENT 'Response body',
    response_code INT NULL COMMENT 'HTTP status code',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    duration_ms INT NULL COMMENT 'Request duration in milliseconds',
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (iot_device_id) REFERENCES iot_devices(id) ON DELETE CASCADE,
    INDEX idx_device_created (iot_device_id, created_at),
    INDEX idx_log_type (log_type),
    INDEX idx_created_at (created_at)
);
```

---

## 🔐 API Authentication Flow

### Request Authentication
1. ESP32 sends request with `X-API-Key` header
2. Middleware validates API key against `iot_devices` table
3. If valid: Update `last_seen_at`, `ip_address`, continue request
4. If invalid: Return 401 Unauthorized, log attempt

### Example Request from ESP32:
```
POST /api/attendance HTTP/1.1
Host: your-server.com
Content-Type: application/json
X-API-Key: your-64-char-api-key-here
Accept: application/json

{
    "card_uid": "A1B2C3D4"
}
```

### API Response Format (existing):
```json
// Success
{
    "status": "success",
    "message": "Absensi berhasil dicatat",
    "user": {
        "name": "John Doe",
        "employee_id": "STD001"
    },
    "subject": "Matematika"
}

// Error
{
    "status": "error",
    "message": "Kartu tidak terdaftar"
}
```

---

## 🔧 Implementation Details

### Middleware: `AuthenticateIotDevice`
```php
namespace App\Http\Middleware;

class AuthenticateIotDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        
        if (blank($apiKey)) {
            return response()->json(['status' => 'error', 'message' => 'API key required'], 401);
        }
        
        $device = IotDevice::where('api_key', $apiKey)
            ->where('status', IotDeviceStatus::ACTIVE)
            ->first();
        
        if (!$device) {
            // Log failed attempt
            return response()->json(['status' => 'error', 'message' => 'Invalid API key'], 401);
        }
        
        // Update last seen
        $device->update([
            'last_seen_at' => now(),
            'ip_address' => $request->ip(),
        ]);
        
        // Attach device to request for logging
        $request->attributes->set('iot_device', $device);
        
        return $next($request);
    }
}
```

### IoT Log Trait (for Controllers)
```php
trait LogsIotActivity
{
    protected function logIotRequest(Request $request, mixed $response, int $durationMs): void
    {
        $device = $request->attributes->get('iot_device');
        
        if ($device) {
            IotLog::create([
                'iot_device_id' => $device->id,
                'log_type' => IotLogType::REQUEST,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'request_payload' => $request->all(),
                'response_payload' => $response instanceof JsonResponse 
                    ? $response->getData(true) 
                    : null,
                'response_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'duration_ms' => $durationMs,
            ]);
        }
    }
}
```

---

## 📊 Admin Panel Features

### IoT Devices Page (`/admin/iot-devices`)
| Column | Description |
|--------|-------------|
| Device Name | Nama device |
| Device Code | Kode unik device |
| Location | Lokasi pemasangan |
| Classroom | Kelas terhubung (opsional) |
| Status | Active/Inactive/Maintenance |
| Last Seen | Waktu koneksi terakhir |
| IP Address | IP terakhir |
| Actions | Edit, View Logs, Regenerate Key, Delete |

**Features:**
- Generate new API key (with confirmation)
- Bulk status update
- Filter by status
- Search by name/code/location

### IoT Logs Page (`/admin/iot-logs`)
| Column | Description |
|--------|-------------|
| Timestamp | Waktu request |
| Device | Nama device |
| Type | Request/Response/Error |
| Endpoint | API endpoint yang diakses |
| Status Code | HTTP response code |
| Duration | Waktu proses (ms) |
| Actions | View Detail |

**Features:**
- Filter by device
- Filter by date range
- Filter by log type
- Filter by status code
- View request/response payload detail
- Export to CSV/Excel

---

## 🔑 Permissions Required

Add to `config/permissions.php`:
```php
'iot' => [
    'iot.view' => 'View IoT Devices',
    'iot.create' => 'Create IoT Device',
    'iot.edit' => 'Edit IoT Device',
    'iot.delete' => 'Delete IoT Device',
    'iot.regenerate-key' => 'Regenerate API Key',
    'iot.logs' => 'View IoT Logs',
    'iot.logs.export' => 'Export IoT Logs',
],
```

---

## 🛡️ Security Considerations

1. **API Key Generation**: Use `Str::random(64)` for secure API keys
2. **Rate Limiting**: Apply rate limiting middleware to API routes
3. **Key Rotation**: Allow admins to regenerate API keys (old key invalidated immediately)
4. **Log Retention**: Implement log cleanup (e.g., delete logs older than 30/90 days)
5. **Sensitive Data**: Never log full card UIDs in plaintext (mask: `A1B2****`)
6. **HTTPS Only**: Enforce HTTPS for all API endpoints in production

---

## 📱 ESP32 Code Update Required

Update `main.cpp` to include API key header:
```cpp
// In sendToAPI() function
http.addHeader("X-API-Key", "YOUR-DEVICE-API-KEY");
```

Store API key in ESP32 Preferences (same as WiFi credentials):
```cpp
String API_KEY = preferences.getString("api_key", "");
```

Add API key configuration to captive portal if needed.

---

## ✅ Acceptance Criteria

### Backend
- [ ] API endpoints secured with API key authentication
- [ ] All API requests logged with full details
- [ ] Device status tracked (last_seen_at updated)
- [ ] CRUD operations for IoT devices work correctly
- [ ] Validation rules enforce data integrity

### Frontend
- [ ] Admin can view list of IoT devices with pagination
- [ ] Admin can add/edit/delete IoT devices
- [ ] Admin can regenerate API keys (with confirmation)
- [ ] Admin can view IoT logs with filters
- [ ] Admin can export logs to CSV

### Security
- [ ] Invalid API key returns 401 Unauthorized
- [ ] Inactive devices cannot access API
- [ ] Failed authentication attempts are logged
- [ ] Rate limiting prevents abuse

---

## 📅 Estimated Effort

| Phase | Components | Est. Time |
|-------|------------|-----------|
| BE - Phase 1 | Migrations, Models, Enums, Repos, Services | 2-3 hours |
| BE - Phase 2 | Middleware, Controllers, Form Requests, Routes | 2-3 hours |
| FE - Phase 3 | Views, JS, Menu, Permissions | 2-3 hours |
| Testing | Integration & Manual Testing | 1-2 hours |
| **Total** | | **7-11 hours** |

---

## Legend
- [ ] = Belum dikerjakan
- [x] = Sudah selesai
