<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('message_id');
            
            // Foreign Keys
            // This links to the 'id' in 'reports' table
            $table->foreignId('report_id')->constrained('reports', 'report_id')->onDelete('cascade');
            
            // This links to the 'id' in 'users' table (the sender)
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            
            $table->text('message_text');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps(); // Standard Laravel tracking
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};