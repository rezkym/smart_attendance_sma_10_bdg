<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CardStatus;
use App\Models\RfidCard;
use App\Repositories\Contracts\RfidCardRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RfidCardService
{
    public function __construct(
        protected RfidCardRepositoryInterface $rfidCardRepository
    ) {}

    /**
     * Get all RFID cards.
     *
     * @return Collection<int, RfidCard>
     */
    public function getAllCards(): Collection
    {
        return $this->rfidCardRepository->getAll();
    }

    /**
     * Get RFID card by ID.
     */
    public function getCardById(int $cardId): ?RfidCard
    {
        return $this->rfidCardRepository->findById($cardId);
    }

    /**
     * Get RFID card by card UID.
     */
    public function getCardByUid(string $cardUid): ?RfidCard
    {
        return $this->rfidCardRepository->findByCardUid($cardUid);
    }

    /**
     * Get all cards assigned to a user.
     *
     * @return Collection<int, RfidCard>
     */
    public function getCardsByUser(int $userId): Collection
    {
        return $this->rfidCardRepository->getByUserId($userId);
    }

    /**
     * Get cards by status.
     *
     * @return Collection<int, RfidCard>
     */
    public function getCardsByStatus(CardStatus $status): Collection
    {
        return $this->rfidCardRepository->getByStatus($status);
    }

    /**
     * Create a new RFID card.
     *
     * @param array{card_uid: string, user_id?: int|null, status: int, issued_at: string, expires_at?: string|null, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function createCard(array $data): RfidCard
    {
        // Check if card UID already exists
        if ($this->rfidCardRepository->existsByCardUid($data['card_uid'])) {
            throw new \InvalidArgumentException("Kartu dengan UID {$data['card_uid']} sudah terdaftar.");
        }

        return DB::transaction(function () use ($data) {
            return $this->rfidCardRepository->create($data);
        });
    }

    /**
     * Update an RFID card.
     *
     * @param array{card_uid?: string, user_id?: int|null, status?: int, issued_at?: string, expires_at?: string|null, notes?: string|null} $data
     *
     * @throws \InvalidArgumentException
     */
    public function updateCard(int $cardId, array $data): RfidCard
    {
        $card = $this->rfidCardRepository->findById($cardId);

        if ($card === null) {
            throw new \InvalidArgumentException("Kartu RFID dengan ID {$cardId} tidak ditemukan.");
        }

        // Check if changing UID would cause duplicate
        if (isset($data['card_uid']) && $data['card_uid'] !== $card->card_uid) {
            if ($this->rfidCardRepository->existsByCardUid($data['card_uid'], $cardId)) {
                throw new \InvalidArgumentException("Kartu dengan UID {$data['card_uid']} sudah terdaftar.");
            }
        }

        return DB::transaction(function () use ($card, $data) {
            return $this->rfidCardRepository->update($card, $data);
        });
    }

    /**
     * Delete an RFID card.
     *
     * @throws \InvalidArgumentException
     */
    public function deleteCard(int $cardId): bool
    {
        $card = $this->rfidCardRepository->findById($cardId);

        if ($card === null) {
            throw new \InvalidArgumentException("Kartu RFID dengan ID {$cardId} tidak ditemukan.");
        }

        // Prevent deleting active cards that are assigned to users
        if ($card->status === CardStatus::ACTIVE && $card->user_id !== null) {
            throw new \InvalidArgumentException('Tidak dapat menghapus kartu aktif yang masih terhubung ke pengguna. Blokir kartu terlebih dahulu.');
        }

        return $this->rfidCardRepository->delete($card);
    }

    /**
     * Assign a card to a user.
     *
     * @throws \InvalidArgumentException
     */
    public function assignCardToUser(int $cardId, int $userId): RfidCard
    {
        $card = $this->rfidCardRepository->findById($cardId);

        if ($card === null) {
            throw new \InvalidArgumentException("Kartu RFID dengan ID {$cardId} tidak ditemukan.");
        }

        if ($card->user_id !== null) {
            throw new \InvalidArgumentException('Kartu sudah terhubung ke pengguna lain. Lepaskan terlebih dahulu.');
        }

        if ($card->status !== CardStatus::ACTIVE) {
            throw new \InvalidArgumentException('Hanya kartu dengan status AKTIF yang dapat dihubungkan ke pengguna.');
        }

        return DB::transaction(function () use ($card, $userId) {
            return $this->rfidCardRepository->update($card, [
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Unassign a card from its user.
     *
     * @throws \InvalidArgumentException
     */
    public function unassignCard(int $cardId): RfidCard
    {
        $card = $this->rfidCardRepository->findById($cardId);

        if ($card === null) {
            throw new \InvalidArgumentException("Kartu RFID dengan ID {$cardId} tidak ditemukan.");
        }

        if ($card->user_id === null) {
            throw new \InvalidArgumentException('Kartu tidak terhubung ke pengguna manapun.');
        }

        return DB::transaction(function () use ($card) {
            return $this->rfidCardRepository->update($card, [
                'user_id' => null,
            ]);
        });
    }

    /**
     * Block an RFID card.
     *
     * @throws \InvalidArgumentException
     */
    public function blockCard(int $cardId, ?string $reason = null): RfidCard
    {
        $card = $this->rfidCardRepository->findById($cardId);

        if ($card === null) {
            throw new \InvalidArgumentException("Kartu RFID dengan ID {$cardId} tidak ditemukan.");
        }

        if ($card->status === CardStatus::BLOCKED) {
            throw new \InvalidArgumentException('Kartu sudah dalam status diblokir.');
        }

        $notes = $card->notes ?? '';
        if ($reason !== null) {
            $notes = trim($notes . "\n[BLOCKED " . Carbon::now()->format('Y-m-d H:i') . "] " . $reason);
        }

        return DB::transaction(function () use ($card, $notes) {
            return $this->rfidCardRepository->update($card, [
                'status' => CardStatus::BLOCKED->value,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Unblock an RFID card (set to ACTIVE).
     *
     * @throws \InvalidArgumentException
     */
    public function unblockCard(int $cardId): RfidCard
    {
        $card = $this->rfidCardRepository->findById($cardId);

        if ($card === null) {
            throw new \InvalidArgumentException("Kartu RFID dengan ID {$cardId} tidak ditemukan.");
        }

        if ($card->status !== CardStatus::BLOCKED) {
            throw new \InvalidArgumentException('Kartu tidak dalam status diblokir.');
        }

        // Check if card is expired
        if ($card->isExpired()) {
            throw new \InvalidArgumentException('Tidak dapat membuka blokir kartu yang sudah kadaluarsa.');
        }

        $notes = trim(($card->notes ?? '') . "\n[UNBLOCKED " . Carbon::now()->format('Y-m-d H:i') . "]");

        return DB::transaction(function () use ($card, $notes) {
            return $this->rfidCardRepository->update($card, [
                'status' => CardStatus::ACTIVE->value,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Mark a card as lost.
     *
     * @throws \InvalidArgumentException
     */
    public function markCardAsLost(int $cardId, ?string $notes = null): RfidCard
    {
        $card = $this->rfidCardRepository->findById($cardId);

        if ($card === null) {
            throw new \InvalidArgumentException("Kartu RFID dengan ID {$cardId} tidak ditemukan.");
        }

        $existingNotes = $card->notes ?? '';
        if ($notes !== null) {
            $existingNotes = trim($existingNotes . "\n[LOST " . Carbon::now()->format('Y-m-d H:i') . "] " . $notes);
        } else {
            $existingNotes = trim($existingNotes . "\n[LOST " . Carbon::now()->format('Y-m-d H:i') . "]");
        }

        return DB::transaction(function () use ($card, $existingNotes) {
            return $this->rfidCardRepository->update($card, [
                'status' => CardStatus::LOST->value,
                'notes' => $existingNotes,
            ]);
        });
    }

    /**
     * Replace a lost/blocked card with a new one.
     * The old card keeps its status, new card is assigned to the same user.
     *
     * @throws \InvalidArgumentException
     */
    public function replaceCard(int $oldCardId, string $newCardUid): RfidCard
    {
        $oldCard = $this->rfidCardRepository->findById($oldCardId);

        if ($oldCard === null) {
            throw new \InvalidArgumentException("Kartu RFID lama dengan ID {$oldCardId} tidak ditemukan.");
        }

        if ($oldCard->status === CardStatus::ACTIVE) {
            throw new \InvalidArgumentException('Kartu aktif tidak perlu diganti. Blokir atau tandai hilang terlebih dahulu.');
        }

        if ($oldCard->user_id === null) {
            throw new \InvalidArgumentException('Kartu lama tidak terhubung ke pengguna manapun.');
        }

        // Check if new card UID already exists
        if ($this->rfidCardRepository->existsByCardUid($newCardUid)) {
            throw new \InvalidArgumentException("Kartu dengan UID {$newCardUid} sudah terdaftar.");
        }

        return DB::transaction(function () use ($oldCard, $newCardUid) {
            // Remove user from old card
            $this->rfidCardRepository->update($oldCard, [
                'user_id' => null,
            ]);

            // Create new card with the same user
            return $this->rfidCardRepository->create([
                'card_uid' => $newCardUid,
                'user_id' => $oldCard->user_id,
                'status' => CardStatus::ACTIVE->value,
                'issued_at' => Carbon::today()->format('Y-m-d'),
                'notes' => "Pengganti kartu #{$oldCard->id} ({$oldCard->card_uid})",
            ]);
        });
    }

    /**
     * Check and update card expiry status.
     */
    public function checkAndUpdateExpiry(RfidCard $card): RfidCard
    {
        if ($card->isExpired() && $card->status !== CardStatus::EXPIRED) {
            return $this->rfidCardRepository->update($card, [
                'status' => CardStatus::EXPIRED->value,
            ]);
        }

        return $card;
    }

    /**
     * Get statistics for dashboard cards.
     *
     * @return array{total: int, active: int, assigned: int, blocked: int}
     */
    public function getStats(): array
    {
        return [
            'total' => $this->rfidCardRepository->getTotalCount(),
            'active' => $this->rfidCardRepository->getActiveCount(),
            'assigned' => $this->rfidCardRepository->getAssignedCount(),
            'blocked' => $this->rfidCardRepository->getByStatus(CardStatus::BLOCKED)->count(),
        ];
    }

    /**
     * Get DataTables query builder.
     *
     * @return Builder<RfidCard>
     */
    public function getDataTableQuery(): Builder
    {
        return $this->rfidCardRepository->getDataTableQuery();
    }

    /**
     * Find an active card by UID for attendance lookup.
     */
    public function findActiveCardByUid(string $cardUid): ?RfidCard
    {
        return $this->rfidCardRepository->findActiveByCardUid($cardUid);
    }

    /**
     * Quick register a card scanned from hardware.
     * Used by the Quick Scan feature.
     *
     * @param array{card_uid: string, user_id: int, expiry_years?: int} $data
     *
     * @throws \InvalidArgumentException
     */
    public function quickRegisterCard(array $data): RfidCard
    {
        $cardUid = strtoupper(trim($data['card_uid']));
        
        // Check if card UID already exists
        if ($this->rfidCardRepository->existsByCardUid($cardUid)) {
            throw new \InvalidArgumentException("Kartu dengan UID {$cardUid} sudah terdaftar.");
        }

        $today = Carbon::today();
        $expiresAt = null;
        
        // Calculate expiry if specified
        if (isset($data['expiry_years']) && $data['expiry_years'] > 0) {
            $expiresAt = $today->copy()->addYears((int) $data['expiry_years'])->format('Y-m-d');
        }

        return DB::transaction(function () use ($cardUid, $data, $today, $expiresAt) {
            return $this->rfidCardRepository->create([
                'card_uid' => $cardUid,
                'user_id' => $data['user_id'],
                'status' => CardStatus::ACTIVE->value,
                'issued_at' => $today->format('Y-m-d'),
                'expires_at' => $expiresAt,
                'notes' => 'Registered via Quick Scan',
            ]);
        });
    }

    // ==========================================
    // QUICK SCAN SERVICE METHODS
    // ==========================================

    /**
     * Start a Quick Scan registration session.
     *
     * @param array{device_id: int, user_id: int, expiry_years: int, device_ip: string} $data
     * @return array{success: bool, session_id?: string, timeout_seconds?: int, message?: string}
     */
    public function startQuickScanSession(array $data): array
    {
        $deviceIp = $data['device_ip'];
        $sessionId = uniqid('reg_', true);
        $timeoutSeconds = (int) config('rfid.quick_scan.timeout_seconds', 30);
        $cachePrefix = config('rfid.quick_scan.cache_prefix', 'rfid_registration_');
        $cacheKey = $cachePrefix . $sessionId;
        $callbackUrl = url("/api/rfid-cards/receive-scan/{$sessionId}");

        try {
            // Use asForm() because ESP32 WebServer expects form-urlencoded, not JSON
            $response = \Illuminate\Support\Facades\Http::timeout(5)->asForm()->post("http://{$deviceIp}/register-mode", [
                'timeout' => $timeoutSeconds,
                'callback_url' => $callbackUrl,
                'session_id' => $sessionId,
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'Device tidak merespon.'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal menghubungi device.'];
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, [
            'device_id' => $data['device_id'],
            'user_id' => $data['user_id'],
            'expiry_years' => $data['expiry_years'],
            'status' => 'waiting',
            'card_uid' => null,
            'error_message' => null,
            'started_at' => now()->timestamp,
        ], $timeoutSeconds + 10);

        return [
            'success' => true,
            'session_id' => $sessionId,
            'timeout_seconds' => $timeoutSeconds,
        ];
    }

    /**
     * Check Quick Scan session status.
     *
     * @return array{success: bool, status: string, card_uid?: string|null, error_message?: string|null, remaining_seconds?: int}
     */
    public function checkQuickScanStatus(string $sessionId): array
    {
        $cachePrefix = config('rfid.quick_scan.cache_prefix', 'rfid_registration_');
        $timeoutSeconds = (int) config('rfid.quick_scan.timeout_seconds', 30);
        $session = \Illuminate\Support\Facades\Cache::get($cachePrefix . $sessionId);

        if ($session === null) {
            return ['success' => false, 'status' => 'expired', 'message' => 'Sesi berakhir.'];
        }

        return [
            'success' => true,
            'status' => $session['status'],
            'card_uid' => $session['card_uid'],
            'error_message' => $session['error_message'] ?? null,
            'remaining_seconds' => max(0, $timeoutSeconds - (now()->timestamp - $session['started_at'])),
        ];
    }

    /**
     * Process a card scanned during Quick Scan registration.
     *
     * @return array{success: bool, message: string, card_id?: int, card_uid?: string, device_id?: int}
     */
    public function processScannedCard(string $sessionId, string $cardUid): array
    {
        $cachePrefix = config('rfid.quick_scan.cache_prefix', 'rfid_registration_');
        $cacheKey = $cachePrefix . $sessionId;
        $session = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($session === null) {
            return ['success' => false, 'message' => 'Sesi tidak ditemukan.'];
        }

        $cardUid = strtoupper(trim($cardUid));
        $deviceId = $session['device_id'] ?? null;

        if ($this->rfidCardRepository->existsByCardUid($cardUid)) {
            $session['status'] = 'error';
            $session['error_message'] = "Kartu {$cardUid} sudah terdaftar.";
            \Illuminate\Support\Facades\Cache::put($cacheKey, $session, 30);
            return ['success' => false, 'message' => $session['error_message'], 'device_id' => $deviceId];
        }

        try {
            $card = $this->quickRegisterCard([
                'card_uid' => $cardUid,
                'user_id' => $session['user_id'],
                'expiry_years' => $session['expiry_years'],
            ]);

            $session['status'] = 'completed';
            $session['card_uid'] = $cardUid;
            \Illuminate\Support\Facades\Cache::put($cacheKey, $session, 30);

            return [
                'success' => true,
                'message' => "Kartu {$cardUid} berhasil didaftarkan.",
                'card_id' => $card->id,
                'card_uid' => $card->card_uid,
                'device_id' => $deviceId,
            ];
        } catch (\InvalidArgumentException $e) {
            $session['status'] = 'error';
            $session['error_message'] = $e->getMessage();
            \Illuminate\Support\Facades\Cache::put($cacheKey, $session, 30);
            return ['success' => false, 'message' => $e->getMessage(), 'device_id' => $deviceId];
        }
    }

    /**
     * Cancel a Quick Scan session.
     */
    public function cancelQuickScanSession(string $sessionId): void
    {
        $cachePrefix = config('rfid.quick_scan.cache_prefix', 'rfid_registration_');
        \Illuminate\Support\Facades\Cache::forget($cachePrefix . $sessionId);
    }
}

