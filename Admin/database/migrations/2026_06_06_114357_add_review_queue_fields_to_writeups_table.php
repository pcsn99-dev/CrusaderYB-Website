<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('writeups')) {
            return;
        }

        Schema::table('writeups', function (Blueprint $table) {
            if (! Schema::hasColumn('writeups', 'review_status')) {
                $table->string('review_status')
                    ->default('pending')
                    ->after('is_done');
            }

            if (! Schema::hasColumn('writeups', 'locked_by')) {
                $table->unsignedInteger('locked_by')
                    ->nullable()
                    ->after('review_status');
            }

            if (! Schema::hasColumn('writeups', 'locked_at')) {
                $table->timestamp('locked_at')
                    ->nullable()
                    ->after('locked_by');
            }

            if (! Schema::hasColumn('writeups', 'is_flagged')) {
                $table->boolean('is_flagged')
                    ->default(false)
                    ->after('locked_at');
            }

            if (! Schema::hasColumn('writeups', 'flag_reason')) {
                $table->text('flag_reason')
                    ->nullable()
                    ->after('is_flagged');
            }

            if (! Schema::hasColumn('writeups', 'flagged_by')) {
                $table->unsignedInteger('flagged_by')
                    ->nullable()
                    ->after('flag_reason');
            }

            if (! Schema::hasColumn('writeups', 'flagged_at')) {
                $table->timestamp('flagged_at')
                    ->nullable()
                    ->after('flagged_by');
            }

            if (! Schema::hasColumn('writeups', 'reviewed_at')) {
                $table->timestamp('reviewed_at')
                    ->nullable()
                    ->after('date_of_proofread');
            }
        });

        DB::table('writeups')
            ->where('is_done', 1)
            ->update([
                'review_status' => 'reviewed',
            ]);

        DB::table('writeups')
            ->where(function ($query) {
                $query->whereNull('is_done')
                    ->orWhere('is_done', 0);
            })
            ->update([
                'review_status' => 'pending',
            ]);

        DB::table('writeups')
            ->whereNotNull('date_of_proofread')
            ->whereNull('reviewed_at')
            ->update([
                'reviewed_at' => DB::raw('date_of_proofread'),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('writeups')) {
            return;
        }

        Schema::table('writeups', function (Blueprint $table) {
            if (Schema::hasColumn('writeups', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }

            if (Schema::hasColumn('writeups', 'flagged_at')) {
                $table->dropColumn('flagged_at');
            }

            if (Schema::hasColumn('writeups', 'flagged_by')) {
                $table->dropColumn('flagged_by');
            }

            if (Schema::hasColumn('writeups', 'flag_reason')) {
                $table->dropColumn('flag_reason');
            }

            if (Schema::hasColumn('writeups', 'is_flagged')) {
                $table->dropColumn('is_flagged');
            }

            if (Schema::hasColumn('writeups', 'locked_at')) {
                $table->dropColumn('locked_at');
            }

            if (Schema::hasColumn('writeups', 'locked_by')) {
                $table->dropColumn('locked_by');
            }

            if (Schema::hasColumn('writeups', 'review_status')) {
                $table->dropColumn('review_status');
            }
        });
    }
};