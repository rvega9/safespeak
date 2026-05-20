<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Step 1: Drop the existing cascade foreign key
            $table->dropForeign(['user_id']);

            // Step 2: Make user_id nullable (needed for nullOnDelete)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Step 3: Re-add the foreign key with nullOnDelete
            // Now when a user is deleted, user_id becomes NULL
            // instead of deleting the report
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Rollback: restore the original cascade foreign key
            $table->dropForeign(['user_id']);

            $table->unsignedBigInteger('user_id')->nullable(false)->change();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};