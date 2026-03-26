<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Notifikasi;
use App\Models\DataKaryawan;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class NotificationSIP extends Command
{
    protected $signature = 'app:notification-warning-sip';
    protected $description = 'Notification Warning SIP';

    private const CATEGORY_ID = 15;
    private const REMINDER_MONTHS = 3;
    private const DOCUMENT_TYPE = 'sip';
    private const TIMEZONE = 'Asia/Jakarta';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = $this->getToday();
        $limitDate = $this->getLimitDate($today);
        $dataKaryawanList = $this->getEligibleEmployees($today, $limitDate);

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($dataKaryawanList as $karyawan) {
            $expireDate = $this->parseExpireDate($karyawan->masa_berlaku_sip, $karyawan->user_id);

            if (!$expireDate) {
                continue;
            }

            $notificationKey = $this->makeNotificationKey((int) $karyawan->user_id, $expireDate);

            if ($this->notificationAlreadySent($notificationKey)) {
                $skippedCount++;
                continue;
            }

            $this->deleteObsoleteNotifications((int) $karyawan->user_id, $notificationKey);
            $this->createNotification((int) $karyawan->user_id, $notificationKey, $expireDate, $today);

            $createdCount++;
        }

        $this->info("Peringatan masa berlaku SIP selesai diproses. Dibuat: {$createdCount}, dilewati: {$skippedCount}.");

        return self::SUCCESS;
    }

    private function getToday(): Carbon
    {
        return Carbon::now(self::TIMEZONE)->startOfDay();
    }

    private function getLimitDate(Carbon $today): Carbon
    {
        return $today->copy()->addMonthsNoOverflow(self::REMINDER_MONTHS)->startOfDay();
    }

    private function getEligibleEmployees(Carbon $today, Carbon $limitDate): Collection
    {
        return DataKaryawan::query()
            ->whereHas('users', function ($query) {
                $query->where('data_completion_step', 0)
                    ->where('status_aktif', 2);
            })
            ->whereNotNull('masa_berlaku_sip')
            ->whereRaw(
                "STR_TO_DATE(masa_berlaku_sip, '%d-%m-%Y') BETWEEN ? AND ?",
                [
                    $today->format('Y-m-d'),
                    $limitDate->format('Y-m-d'),
                ]
            )
            ->get();
    }

    private function parseExpireDate(?string $date, int $userId): ?Carbon
    {
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', trim($date), self::TIMEZONE)->startOfDay();
        } catch (\Throwable $th) {
            $this->warn("Format masa_berlaku_sip tidak valid untuk user_id {$userId}: {$date}");
            return null;
        }
    }

    private function makeNotificationKey(int $userId, Carbon $expireDate): string
    {
        return sprintf(
            '%s:%d:%s',
            self::DOCUMENT_TYPE,
            $userId,
            $expireDate->format('Y-m-d')
        );
    }

    private function notificationAlreadySent(string $notificationKey): bool
    {
        return Notifikasi::query()
            ->where('notification_key', $notificationKey)
            ->exists();
    }

    private function deleteObsoleteNotifications(int $userId, string $currentNotificationKey): void
    {
        Notifikasi::query()
            ->where('user_id', $userId)
            ->where('kategori_notifikasi_id', self::CATEGORY_ID)
            ->where(function ($query) use ($currentNotificationKey) {
                $query->where(function ($subQuery) use ($currentNotificationKey) {
                    $subQuery->whereNotNull('notification_key')
                        ->where('notification_key', 'like', self::DOCUMENT_TYPE . ':%')
                        ->where('notification_key', '!=', $currentNotificationKey);
                })->orWhere(function ($subQuery) {
                    $subQuery->whereNull('notification_key')
                        ->where('message', 'like', '%Peringatan: Masa berlaku SIP%');
                });
            })
            ->delete();
    }

    private function createNotification(int $userId, string $notificationKey, Carbon $expireDate, Carbon $today): void
    {
        Notifikasi::create([
            'kategori_notifikasi_id' => self::CATEGORY_ID,
            'user_id' => $userId,
            'message' => $this->buildMessage($expireDate),
            'notification_key' => $notificationKey,
            'is_read' => false,
            'is_verifikasi' => false,
            'created_at' => $today->copy(),
            'updated_at' => $today->copy(),
        ]);
    }

    private function buildMessage(Carbon $expireDate): string
    {
        return "Peringatan: Masa berlaku SIP Anda akan berakhir pada {$expireDate->format('d-m-Y')}.";
    }
}
