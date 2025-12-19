<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CardStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RfidCard\BlockRfidCardRequest;
use App\Http\Requests\RfidCard\CancelQuickScanRequest;
use App\Http\Requests\RfidCard\CheckQuickScanRequest;
use App\Http\Requests\RfidCard\StartQuickScanRequest;
use App\Http\Requests\RfidCard\StoreRfidCardRequest;
use App\Http\Requests\RfidCard\UpdateRfidCardRequest;
use App\Services\IotDeviceService;
use App\Services\RfidCardService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RfidCardController extends Controller
{

    public function __construct(
        protected RfidCardService $rfidCardService,
        protected UserService $userService,
        protected IotDeviceService $iotDeviceService
    ) {}

    /**
     * Display RFID cards list page.
     */
    public function index(): View
    {
        $stats = $this->rfidCardService->getStats();
        $cardStatuses = CardStatus::toArray();

        return view('content.pages.admin.rfid-cards', compact('stats', 'cardStatuses'));
    }

    /**
     * DataTables server-side data.
     */
    public function list(Request $request): JsonResponse
    {
        $query = $this->rfidCardService->getDataTableQuery();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('assigned')) {
            if ($request->input('assigned') === 'yes') {
                $query->whereNotNull('user_id');
            } else {
                $query->whereNull('user_id');
            }
        }

        return DataTables::eloquent($query)
            ->addColumn('user_name', fn($card) => $card->user?->display_name ?? '-')
            ->addColumn('user_username', fn($card) => $card->user?->name ?? '-')
            ->addColumn('user_email', fn($card) => $card->user?->email ?? '-')
            ->addColumn('status_label', fn($card) => $card->status->label())
            ->addColumn('status_badge', fn($card) => $card->status->badgeClass())
            ->addColumn('is_assigned', fn($card) => $card->user_id !== null)
            ->addColumn('issued_at_formatted', fn($card) => $card->issued_at->format('d M Y'))
            ->addColumn('expires_at_formatted', fn($card) => $card->expires_at?->format('d M Y') ?? '-')
            ->addColumn('is_expired', fn($card) => $card->isExpired())
            ->addColumn('is_usable', fn($card) => $card->isUsable())
            ->addColumn('actions', fn($card) => $card->id)
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Get available statuses for dropdown.
     */
    public function statuses(): JsonResponse
    {
        $statuses = [];
        foreach (CardStatus::cases() as $status) {
            $statuses[] = [
                'value' => $status->value,
                'label' => $status->label(),
                'badge_class' => $status->badgeClass(),
            ];
        }

        return response()->json(['success' => true, 'data' => $statuses]);
    }

    /**
     * Get available users for assignment dropdown.
     */
    public function availableUsers(Request $request): JsonResponse
    {
        $users = $this->userService->getAllUsers();

        // Use display_name for full name display
        $formatted = $users->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->display_name,
            'email' => $user->email,
        ]);

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    /**
     * Get users without registered RFID cards (for Quick Scan).
     * Filters out users who already have an active RFID card.
     */
    public function availableUsersWithoutCard(): JsonResponse
    {
        $users = $this->userService->getAllUsers();

        // Get user IDs that already have active RFID cards
        $userIdsWithCards = $this->rfidCardService->getDataTableQuery()
            ->whereNotNull('user_id')
            ->where('status', \App\Enums\CardStatus::ACTIVE->value)
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // Filter out users who already have cards
        $usersWithoutCards = $users->filter(fn($user) => !in_array($user->id, $userIdsWithCards));

        // Use display_name for search/display, not raw name column
        $formatted = $usersWithoutCards->map(fn($user) => [
            'id' => $user->id,
            'name' => $user->display_name,
            'email' => $user->email,
        ])->values();

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    /**
     * Get RFID card assigned to a specific user.
     * Used by students form to display card info.
     */
    public function cardByUser(int $userId): JsonResponse
    {
        $card = $this->rfidCardService->getDataTableQuery()
            ->where('user_id', $userId)
            ->where('status', \App\Enums\CardStatus::ACTIVE->value)
            ->first();

        if ($card === null) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active RFID card found for this user.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $card->id,
                'card_uid' => $card->card_uid,
                'status' => $card->status->value,
                'status_label' => $card->status->label(),
                'status_badge' => $card->status->badgeClass(),
                'issued_at' => $card->issued_at->format('d M Y'),
                'expires_at' => $card->expires_at?->format('d M Y'),
                'is_expired' => $card->isExpired(),
            ],
        ]);
    }

    /**
     * Get available IoT devices for Quick Scan.
     */
    public function availableDevices(): JsonResponse
    {
        $devices = $this->iotDeviceService->getAllActiveDevices();

        $formatted = $devices->map(fn($device) => [
            'id' => $device->id,
            'name' => $device->name,
            'device_code' => $device->device_code,
            'location' => $device->location,
            'ip_address' => $device->ip_address,
        ]);

        return response()->json(['success' => true, 'data' => $formatted]);
    }

    /**
     * Store a new RFID card.
     */
    public function store(StoreRfidCardRequest $request): JsonResponse
    {
        try {
            $card = $this->rfidCardService->createCard([
                'card_uid' => $request->validated('card_uid'),
                'user_id' => $request->validated('user_id'),
                'status' => $request->validated('status'),
                'issued_at' => $request->validated('issued_at'),
                'expires_at' => $request->validated('expires_at'),
                'notes' => $request->validated('notes'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Kartu RFID {$card->card_uid} berhasil ditambahkan.",
                'data' => $card,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get RFID card data for editing.
     */
    public function show(int $rfidCard): JsonResponse
    {
        $card = $this->rfidCardService->getCardById($rfidCard);

        if ($card === null) {
            return response()->json(['success' => false, 'message' => 'Kartu RFID tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $card->id,
                'card_uid' => $card->card_uid,
                'user_id' => $card->user_id,
                'user_name' => $card->user?->name,
                'status' => $card->status->value,
                'status_label' => $card->status->label(),
                'issued_at' => $card->issued_at->format('Y-m-d'),
                'expires_at' => $card->expires_at?->format('Y-m-d'),
                'notes' => $card->notes,
                'is_expired' => $card->isExpired(),
                'is_usable' => $card->isUsable(),
            ],
        ]);
    }

    /**
     * Update an existing RFID card.
     */
    public function update(UpdateRfidCardRequest $request, int $rfidCard): JsonResponse
    {
        try {
            $updatedCard = $this->rfidCardService->updateCard(
                $rfidCard,
                array_filter([
                    'card_uid' => $request->validated('card_uid'),
                    'user_id' => $request->has('user_id') ? $request->validated('user_id') : null,
                    'status' => $request->validated('status'),
                    'issued_at' => $request->validated('issued_at'),
                    'expires_at' => $request->validated('expires_at'),
                    'notes' => $request->validated('notes'),
                ], fn($value) => $value !== null)
            );

            return response()->json([
                'success' => true,
                'message' => "Kartu RFID {$updatedCard->card_uid} berhasil diperbarui.",
                'data' => $updatedCard,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete an RFID card.
     */
    public function destroy(int $rfidCard): JsonResponse
    {
        try {
            $this->rfidCardService->deleteCard($rfidCard);
            return response()->json(['success' => true, 'message' => 'Kartu RFID berhasil dihapus.']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Block an RFID card.
     */
    public function block(BlockRfidCardRequest $request, int $rfidCard): JsonResponse
    {
        try {
            $card = $this->rfidCardService->blockCard($rfidCard, $request->validated('reason'));

            return response()->json([
                'success' => true,
                'message' => "Kartu RFID {$card->card_uid} berhasil diblokir.",
                'data' => $card,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Unblock an RFID card.
     */
    public function unblock(int $rfidCard): JsonResponse
    {
        try {
            $card = $this->rfidCardService->unblockCard($rfidCard);

            return response()->json([
                'success' => true,
                'message' => "Kartu RFID {$card->card_uid} berhasil dibuka blokirnya.",
                'data' => $card,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get stats for dashboard cards.
     */
    public function stats(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->rfidCardService->getStats()]);
    }

    // ==========================================
    // QUICK SCAN REGISTRATION ENDPOINTS
    // ==========================================

    /**
     * Start registration mode on a device.
     */
    public function startRegistrationMode(StartQuickScanRequest $request): JsonResponse
    {
        $device = $this->iotDeviceService->getDeviceById($request->getDeviceId());

        if ($device === null || !$device->isActive()) {
            return response()->json(['success' => false, 'message' => 'Device tidak aktif.'], 422);
        }

        if (blank($device->ip_address)) {
            return response()->json(['success' => false, 'message' => 'Device tidak memiliki IP.'], 422);
        }

        $result = $this->rfidCardService->startQuickScanSession([
            'device_id' => $request->getDeviceId(),
            'user_id' => $request->getUserId(),
            'expiry_years' => $request->getExpiryYears(),
            'device_ip' => $device->ip_address,
        ]);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device siap untuk scan kartu.',
            'data' => [
                'session_id' => $result['session_id'],
                'timeout_seconds' => $result['timeout_seconds'],
            ],
        ]);
    }

    /**
     * Check registration status (polling endpoint).
     */
    public function checkRegistrationStatus(CheckQuickScanRequest $request): JsonResponse
    {
        $result = $this->rfidCardService->checkQuickScanStatus($request->getSessionId());
        return response()->json($result);
    }

    /**
     * Receive scanned card from ESP32.
     * Logs the action to IoT logs for visibility.
     */
    public function receiveScannedCard(Request $request, string $sessionId): JsonResponse
    {
        $startTime = microtime(true);
        $request->validate(['card_uid' => 'required|string|max:50']);

        $result = $this->rfidCardService->processScannedCard(
            $sessionId,
            $request->input('card_uid')
        );

        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        // Log to IoT logs if we have a device ID
        if (isset($result['device_id']) && $result['device_id'] !== null) {
            $iotLogService = app(\App\Services\IotLogService::class);
            $iotLogService->logRequest(
                deviceId: $result['device_id'],
                endpoint: 'api/rfid-cards/receive-scan/' . $sessionId,
                method: 'POST',
                requestPayload: ['card_uid' => $request->input('card_uid'), 'session_id' => $sessionId],
                responsePayload: $result,
                responseCode: $result['success'] ? 200 : 422,
                ipAddress: $request->ip() ?? 'unknown',
                userAgent: $request->userAgent(),
                durationMs: $durationMs
            );
        }

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => ['card_id' => $result['card_id'], 'card_uid' => $result['card_uid']],
        ]);
    }

    /**
     * Cancel registration session.
     */
    public function cancelRegistration(CancelQuickScanRequest $request): JsonResponse
    {
        $this->rfidCardService->cancelQuickScanSession($request->getSessionId());
        return response()->json(['success' => true, 'message' => 'Sesi dibatalkan.']);
    }
}
