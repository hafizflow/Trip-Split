<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trip_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['creator', 'admin', 'member'])->default('member');
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['trip_id', 'user_id']);
            $table->index(['trip_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_members');
    }
};
