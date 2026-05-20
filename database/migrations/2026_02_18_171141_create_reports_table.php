<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('reports', function (Blueprint $table) {
        $table->id('report_id');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->string('case_id')->unique();
        $table->timestamp('occurred_at');
        $table->text('description');
        $table->string('status')->default('pending');
        $table->timestamps();
    });
}

};
