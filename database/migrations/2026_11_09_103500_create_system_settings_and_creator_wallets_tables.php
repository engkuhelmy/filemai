<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. System Settings Table
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->string('type', 30)->default('string');
                $table->string('group', 50)->default('monetization');
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });

            // Seed initial settings
            $now = now();
            DB::table('system_settings')->insert([
                [
                    'key' => 'viewer_vip_monthly_price',
                    'value' => '14.90',
                    'type' => 'decimal',
                    'group' => 'subscription',
                    'description' => 'Harga langganan bulanan penonton (Pas VIP Tanpa Iklan - RM / bulan)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'viewer_vip_yearly_price',
                    'value' => '129.90',
                    'type' => 'decimal',
                    'group' => 'subscription',
                    'description' => 'Harga langganan tahunan penonton (Pas VIP Tanpa Iklan - RM / tahun)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'creator_package_price',
                    'value' => '50.00',
                    'type' => 'decimal',
                    'group' => 'creator_package',
                    'description' => 'Pakej yuran pengesahan & pendaftaran menjadi video creator (RM sekali / akaun)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'creator_earnings_rate_per_block',
                    'value' => '0.000100',
                    'type' => 'decimal',
                    'group' => 'creator_earnings',
                    'description' => 'Pulangan setiap blok tontonan layak yang diterima creator (RM / block)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'block_duration_seconds',
                    'value' => '60',
                    'type' => 'integer',
                    'group' => 'creator_earnings',
                    'description' => 'Tempoh saat bagi satu (1) blok tontonan layak (60 saat = 1 minit penuh)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key' => 'min_payout_amount',
                    'value' => '10.00',
                    'type' => 'decimal',
                    'group' => 'payout',
                    'description' => 'Had minima pengeluaran baki royalti tontonan oleh creator (RM)',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        // 2. Creator Wallets Table (Collection: Terkumpul, Dikeluarkan, Baki)
        if (!Schema::hasTable('creator_wallets')) {
            Schema::create('creator_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->decimal('total_earned', 14, 6)->default(0.000000)->comment('Duit Terkumpul');
                $table->decimal('total_withdrawn', 14, 6)->default(0.000000)->comment('Duit Telah Dikeluarkan');
                $table->decimal('current_balance', 14, 6)->default(0.000000)->comment('Baki Semasa Boleh Keluar');
                $table->unsignedBigInteger('total_blocks')->default(0)->comment('Jumlah Blok Tontonan Layak');
                $table->timestamps();

                $table->foreign('user_id', 'fk_cw_user')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 3. Creator Earning Logs (Per view block audit trail)
        if (!Schema::hasTable('creator_earning_logs')) {
            Schema::create('creator_earning_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creator_id')->index('idx_cel_creator');
                $table->unsignedBigInteger('drama_id')->index('idx_cel_drama');
                $table->unsignedBigInteger('episode_id')->index('idx_cel_episode');
                $table->unsignedInteger('duration_seconds')->default(0);
                $table->unsignedInteger('blocks_count')->default(0);
                $table->decimal('rate_per_block', 12, 6)->default(0.000100);
                $table->decimal('amount', 14, 6)->default(0.000000);
                $table->timestamp('created_at')->useCurrent()->index('idx_cel_created');

                $table->foreign('creator_id', 'fk_cel_creator')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('drama_id', 'fk_cel_drama')->references('id')->on('dramas')->onDelete('cascade');
                $table->foreign('episode_id', 'fk_cel_episode')->references('id')->on('episodes')->onDelete('cascade');
            });
        }

        // 4. Creator Payout Requests Table
        if (!Schema::hasTable('creator_payouts')) {
            Schema::create('creator_payouts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creator_id')->index('idx_cp_creator');
                $table->decimal('amount', 12, 2);
                $table->string('bank_name', 100);
                $table->string('account_number', 100);
                $table->string('account_holder', 150);
                $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
                $table->string('reference_no', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('creator_id', 'fk_cp_creator')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_payouts');
        Schema::dropIfExists('creator_earning_logs');
        Schema::dropIfExists('creator_wallets');
        Schema::dropIfExists('system_settings');
    }
};
