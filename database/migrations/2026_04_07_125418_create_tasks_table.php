<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{public function up(): void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();

        // Status transitions logic [cite: 57-62, 70]
        $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'APPROVED', 'REJECTED'])
              ->default('PENDING');

        // Audit Tracking: Ke task-ta banalo [cite: 75, 82]
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // Audit Tracking: Ke approve/update korlo [cite: 76, 82]
        $table->unsignedBigInteger('updated_by')->nullable();

        $table->softDeletes(); // Requirement: Soft delete task
        $table->timestamps(); // createdAt, updatedAt [cite: 78-79]
    });
}
};
