<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vacation_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('default_days_per_year')->default(20);
            $table->boolean('allow_carryover')->default(true);
            $table->integer('max_carryover_days')->default(5);
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('vacation_settings')->insert([
            'default_days_per_year' => 20,
            'allow_carryover' => true,
            'max_carryover_days' => 5,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('vacation_settings');
    }
};