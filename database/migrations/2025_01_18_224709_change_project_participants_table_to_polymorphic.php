<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('project_participants', function (Blueprint $table) {
            $table->string('project_type')->default('App\Models\Project');
            $table->enum('type', ['organizer', 'staff', 'attendee'])->nullable()->change();
        });
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('participants');
            $table->dropColumn('role');
        });
    }
};
