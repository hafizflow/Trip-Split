<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('added_by_user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('trip_id');
            $table->index('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
