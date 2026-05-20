<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved', 'withdrawn') DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE reports MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved') DEFAULT 'pending'");
    }
};
