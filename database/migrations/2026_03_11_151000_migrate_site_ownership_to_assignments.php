<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('sites', 'owner_id')) {
            return;
        }

        $now = now();

        DB::table('sites')
            ->select('id', 'owner_id')
            ->whereNotNull('owner_id')
            ->orderBy('id')
            ->each(function (object $site) use ($now): void {
                DB::table('site_user')->updateOrInsert(
                    [
                        'site_id' => $site->id,
                        'user_id' => $site->owner_id,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('sites', 'owner_id')) {
            return;
        }

        Schema::table('sites', function (Blueprint $table) {
            $table->foreignUuid('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        DB::table('sites')
            ->select('id')
            ->orderBy('id')
            ->each(function (object $site): void {
                $ownerId = DB::table('site_user')
                    ->where('site_id', $site->id)
                    ->orderBy('created_at')
                    ->value('user_id');

                DB::table('sites')
                    ->where('id', $site->id)
                    ->update(['owner_id' => $ownerId]);
            });
    }
};
