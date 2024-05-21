<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('duration', 4, 1)->nullable(); // use decimal for exact value
            $table->string('estimated_attendees', 50)->nullable();
        });
    }
};
