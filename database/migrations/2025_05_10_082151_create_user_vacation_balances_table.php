<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_vacation_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->decimal('total_days', 5, 1)->default(0);
            $table->decimal('used_days', 5, 1)->default(0);
            $table->decimal('carryover_days', 5, 1)->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_vacation_balances');
    }
};