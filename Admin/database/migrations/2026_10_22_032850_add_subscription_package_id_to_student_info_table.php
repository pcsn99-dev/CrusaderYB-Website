<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_info', function (Blueprint $table) {
            $table->unsignedBigInteger(
                'subscription_package_id'
            )->nullable()->after('is_subscribe');
        });

        // Default to package 1 for students without
        // an existing legacy subscription.
        DB::table('student_info')->update([
            'subscription_package_id' => 1,
        ]);

        // Copy existing package selections from
        // the legacy account_subscription table.
        $legacySubscriptions = DB::table('account_subscription')
            ->select('student_info_id', 'subs_id')
            ->get();

        foreach ($legacySubscriptions as $subscription) {
            DB::table('student_info')
                ->where('id', $subscription->student_info_id)
                ->update([
                    'subscription_package_id' =>
                        $subscription->subs_id,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('student_info', function (Blueprint $table) {
            $table->dropColumn(
                'subscription_package_id'
            );
        });
    }
};