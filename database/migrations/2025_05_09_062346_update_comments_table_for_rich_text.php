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
        Schema::table('comments', function (Blueprint $table) {
            // Modify content column to TEXT type if it's not already
            $table->text('content')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If needed, you can revert the column type
        Schema::table('comments', function (Blueprint $table) {
            $table->string('content')->change();
        });
    }
};