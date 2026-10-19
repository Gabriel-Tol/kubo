<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

class PaymentProofService
{
    public function __construct(private readonly BaseConnection $db, private readonly ?string $storagePath = null)
    {
    }

    public function submit(int $orderId, int $userId, UploadedFile $file): int
    {
        $order = $this->db->table('orders')->where('id', $orderId)->where('user_id', $userId)->get()->getRowArray();
        if ($order === null) {
            throw new InvalidArgumentException('Pesanan tidak ditemukan.');
        }
        (new ExpiryService($this->db))->expireIfDue($orderId, new DateTimeImmutable());
        $order = $this->db->table('orders')->where('id', $orderId)->get()->getRowArray();
        if ($order['status'] !== 'pending_payment') {
            throw new InvalidArgumentException('Bukti hanya dapat dikirim untuk pesanan yang menunggu pembayaran.');
        }
        if (! $file->isValid() || $file->hasMoved()) {
            throw new InvalidArgumentException('Pilih berkas bukti pembayaran yang valid.');
        }
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('Ukuran bukti maksimal 2 MB.');
        }
        $mime = strtolower((string) $file->getMimeType());
        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new InvalidArgumentException('Format bukti harus JPG atau PNG.');
        }

        $directory = $this->storagePath ?? WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'payment-proofs';
        if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
            throw new RuntimeException('Penyimpanan bukti tidak dapat disiapkan.');
        }
        $fileName = bin2hex(random_bytes(24)) . ($mime === 'image/png' ? '.png' : '.jpg');
        $file->move($directory, $fileName);
        $now = date('Y-m-d H:i:s');
        $this->db->table('payment_proofs')->insert([
            'order_id' => $orderId, 'file_path' => $fileName,
            'original_name' => mb_substr(basename($file->getClientName()), 0, 255),
            'mime_type' => $mime, 'file_size' => $file->getSize(), 'status' => 'submitted',
            'created_at' => $now,
        ]);
        return (int) $this->db->insertID();
    }

    public function review(int $proofId, int $adminId, bool $accepted, ?string $reason = null): void
    {
        $this->db->transBegin();
        try {
            $proof = $this->db->table('payment_proofs')->where('id', $proofId)->get()->getRowArray();
            if ($proof === null || $proof['status'] !== 'submitted') {
                throw new InvalidArgumentException('Bukti tidak ditemukan atau sudah diperiksa.');
            }
            if (! $accepted && trim((string) $reason) === '') {
                throw new InvalidArgumentException('Alasan penolakan bukti wajib diisi.');
            }
            if ($accepted) {
                (new OrderStatusService($this->db))->transition((int) $proof['order_id'], 'confirmed', $adminId, 'Pembayaran dikonfirmasi setelah pemeriksaan mutasi.');
            }
            $this->db->table('payment_proofs')->where('id', $proofId)->update([
                'status' => $accepted ? 'accepted' : 'rejected', 'reviewed_by' => $adminId,
                'review_reason' => $accepted ? null : trim((string) $reason), 'reviewed_at' => date('Y-m-d H:i:s'),
            ]);
            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Hasil pemeriksaan bukti gagal disimpan.');
            }
            $this->db->transCommit();
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }
}