<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. User Profile & Bank Payout Details (Ciri 3, 6, 7)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'headline')) {
                $table->string('headline', 150)->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('headline');
            }
            if (!Schema::hasColumn('users', 'cover_photo_url')) {
                $table->string('cover_photo_url', 255)->nullable()->after('avatar_url');
            }
            if (!Schema::hasColumn('users', 'specialties')) {
                $table->text('specialties')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'social_links')) {
                $table->text('social_links')->nullable()->after('specialties');
            }
            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('social_links');
            }
            if (!Schema::hasColumn('users', 'bank_account_number')) {
                $table->string('bank_account_number', 100)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('users', 'bank_account_holder')) {
                $table->string('bank_account_holder', 150)->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('users', 'bank_ic_ssm')) {
                $table->string('bank_ic_ssm', 50)->nullable()->after('bank_account_holder');
            }
            if (!Schema::hasColumn('users', 'is_bank_verified')) {
                $table->boolean('is_bank_verified')->default(false)->after('bank_ic_ssm');
            }
        });

        // 2. Creator Wallets additions (Ciri 1)
        if (Schema::hasTable('creator_wallets')) {
            Schema::table('creator_wallets', function (Blueprint $table) {
                if (!Schema::hasColumn('creator_wallets', 'pending_balance')) {
                    $table->decimal('pending_balance', 14, 6)->default(0)->after('current_balance');
                }
            });
        }

        // 3. Creator Earning Logs additions (Ciri 2)
        if (Schema::hasTable('creator_earning_logs')) {
            Schema::table('creator_earning_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('creator_earning_logs', 'earning_type')) {
                    $table->enum('earning_type', ['ad_share', 'paywall', 'season_pass', 'tip'])->default('ad_share')->after('amount');
                }
                if (!Schema::hasColumn('creator_earning_logs', 'description')) {
                    $table->string('description', 255)->nullable()->after('earning_type');
                }
            });
        }

        // 4. Fan Tips (Ciri 9)
        if (!Schema::hasTable('fan_tips')) {
            Schema::create('fan_tips', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creator_id')->index();
                $table->unsignedBigInteger('drama_id')->nullable()->index();
                $table->unsignedBigInteger('episode_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('fan_name', 100);
                $table->string('fan_email', 150)->nullable();
                $table->decimal('amount', 10, 2);
                $table->text('message')->nullable();
                $table->enum('status', ['pending', 'completed'])->default('completed');
                $table->timestamps();
            });
        }

        // 5. Community Hub Comments (Ciri 10)
        if (!Schema::hasTable('episode_comments')) {
            Schema::create('episode_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('drama_id')->index();
                $table->unsignedBigInteger('episode_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('author_name', 100);
                $table->text('content');
                $table->boolean('is_pinned')->default(false);
                $table->text('creator_reply')->nullable();
                $table->unsignedInteger('likes_count')->default(0);
                $table->timestamps();
            });
        }

        // 6. Promo Codes (Ciri 11)
        if (!Schema::hasTable('promo_codes')) {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creator_id')->index();
                $table->unsignedBigInteger('drama_id')->nullable()->index();
                $table->string('code', 50)->unique();
                $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
                $table->decimal('discount_value', 8, 2);
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('times_used')->default(0);
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 7. Activity Log & Notifications (Ciri 13)
        if (!Schema::hasTable('creator_notifications')) {
            Schema::create('creator_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->enum('type', ['earning', 'payout', 'tip', 'system', 'comment'])->default('system');
                $table->string('title', 255);
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        // 8. Team / Role Management (Ciri 14)
        if (!Schema::hasTable('creator_team_members')) {
            Schema::create('creator_team_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creator_id')->index();
                $table->string('name', 150);
                $table->string('email', 150);
                $table->enum('role', ['editor', 'uploader', 'moderator'])->default('editor');
                $table->text('permissions')->nullable();
                $table->enum('status', ['active', 'invited'])->default('active');
                $table->timestamps();
            });
        }

        // 9. Support & Takedown Tickets (Ciri 15)
        if (!Schema::hasTable('creator_support_tickets')) {
            Schema::create('creator_support_tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('creator_id')->index();
                $table->enum('type', ['takedown', 'payout', 'technical', 'general'])->default('general');
                $table->string('subject', 255);
                $table->text('description');
                $table->unsignedBigInteger('drama_id')->nullable()->index();
                $table->string('proof_url', 500)->nullable();
                $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_support_tickets');
        Schema::dropIfExists('creator_team_members');
        Schema::dropIfExists('creator_notifications');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('episode_comments');
        Schema::dropIfExists('fan_tips');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'headline', 'bio', 'cover_photo_url', 'specialties',
                'social_links', 'bank_name', 'bank_account_number',
                'bank_account_holder', 'bank_ic_ssm', 'is_bank_verified'
            ]);
        });
    }
};
