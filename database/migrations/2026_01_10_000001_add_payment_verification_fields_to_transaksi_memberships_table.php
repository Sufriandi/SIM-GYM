<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan tabel ada
        if (!Schema::hasTable('transaksi_memberships')) {
            return;
        }

        Schema::table('transaksi_memberships', function (Blueprint $table) {
            // ====== PAYMENT VERIFICATION FIELDS ======
            // Hindari nama "status" karena sudah ada accessor getStatusAttribute()

            if (!Schema::hasColumn('transaksi_memberships', 'payment_status')) {
                // pending: dibuat tapi belum upload bukti
                // submitted: bukti diupload (menunggu konfirmasi admin)
                // confirmed: admin setuju
                // rejected: admin tolak
                // expired: lewat batas waktu pembayaran (opsional)
                $table->string('payment_status', 20)->default('pending')->after('metode_pembayaran');
                $table->index('payment_status');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'expires_at')) {
                // batas waktu upload bukti (misal 30 menit / 1 jam)
                $table->timestamp('expires_at')->nullable()->after('payment_status');
                $table->index('expires_at');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'paid_at')) {
                // waktu bukti bayar diupload
                $table->timestamp('paid_at')->nullable()->after('expires_at');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('paid_at');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('confirmed_at');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'verified_by')) {
                // admin user id yang verifikasi
                $table->unsignedBigInteger('verified_by')->nullable()->after('rejected_at');
                $table->index('verified_by');
                // FK opsional (kalau tabel users kamu pakai bigIncrements)
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('transaksi_memberships', 'payment_proof_path')) {
                // path file bukti bayar (storage/public/...)
                $table->string('payment_proof_path', 255)->nullable()->after('verified_by');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'payment_proof_original')) {
                $table->string('payment_proof_original', 255)->nullable()->after('payment_proof_path');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'payment_proof_mime')) {
                $table->string('payment_proof_mime', 100)->nullable()->after('payment_proof_original');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'payment_proof_size')) {
                $table->unsignedBigInteger('payment_proof_size')->nullable()->after('payment_proof_mime');
            }

            if (!Schema::hasColumn('transaksi_memberships', 'verification_note')) {
                // catatan admin saat reject/confirm
                $table->string('verification_note', 255)->nullable()->after('payment_proof_size');
            }

            // ====== OPTIONAL: RAPKAN INDEX PENTING ======
            if (!Schema::hasColumn('transaksi_memberships', 'no_nota')) {
                // kalau ternyata belum ada (harusnya sudah ada)
                $table->string('no_nota', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transaksi_memberships')) {
            return;
        }

        Schema::table('transaksi_memberships', function (Blueprint $table) {
            // drop FK dulu sebelum drop kolom
            if (Schema::hasColumn('transaksi_memberships', 'verified_by')) {
                try {
                    $table->dropForeign(['verified_by']);
                } catch (\Throwable $e) {
                    // jika FK tidak ada, abaikan
                }
            }

            $dropCols = [
                'payment_status',
                'expires_at',
                'paid_at',
                'confirmed_at',
                'rejected_at',
                'verified_by',
                'payment_proof_path',
                'payment_proof_original',
                'payment_proof_mime',
                'payment_proof_size',
                'verification_note',
            ];

            foreach ($dropCols as $col) {
                if (Schema::hasColumn('transaksi_memberships', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
