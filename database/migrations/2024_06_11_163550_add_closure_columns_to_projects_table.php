<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('closure_reminded_at')->nullable();
            $table->timestamp('closure_submitted_at')->nullable();
            $table->foreignId('closure_submitted_by')->nullable()->constrained('users');
            $table->tinyInteger('closure_approved_status')->default(0);
            $table->timestamp('closure_approved_at')->nullable();
            $table->text('closure_approved_message')->nullable();
            $table->foreignId('closure_approved_by')->nullable()->constrained('users');
        });
        Schema::table('project_participants', function (Blueprint $table) {
            $table->tinyInteger('verify_status')->default(0);
            $table->text('reject_reason')->nullable();
            $table->json('reject_participants')->default('[]');
            $table->tinyInteger('approve_status')->default(0);
        });
    }
};
