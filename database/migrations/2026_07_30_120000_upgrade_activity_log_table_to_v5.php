<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * spatie/laravel-activitylog v5 splits the old `properties` column in two:
 * tracked model changes ("attributes"/"old") move to `attribute_changes`,
 * while `properties` keeps only custom data set via withProperties().
 * The batch system is gone, so `batch_uuid` is dropped.
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->json('attribute_changes')->nullable();
        });

        DB::table('activity_log')
            ->whereNotNull('properties')
            ->eachById(function ($row) {
                $properties = json_decode($row->properties, true);
                if (!is_array($properties)) {
                    return;
                }

                $changeKeys = array_flip(['attributes', 'old']);
                $changes = array_intersect_key($properties, $changeKeys);
                $remaining = array_diff_key($properties, $changeKeys);

                DB::table('activity_log')
                    ->where('id', $row->id)
                    ->update([
                        'attribute_changes' => $changes === [] ? null : json_encode($changes),
                        'properties' => $remaining === [] ? null : json_encode($remaining),
                    ]);
            });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn('batch_uuid');
        });
    }

    public function down(): void {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->uuid('batch_uuid')->nullable();
        });

        DB::table('activity_log')
            ->whereNotNull('attribute_changes')
            ->eachById(function ($row) {
                $changes = json_decode($row->attribute_changes, true) ?: [];
                $properties = json_decode($row->properties ?? '[]', true) ?: [];

                DB::table('activity_log')
                    ->where('id', $row->id)
                    ->update(['properties' => json_encode(array_merge($properties, $changes))]);
            });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn('attribute_changes');
        });
    }
};
