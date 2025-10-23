<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('friend_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted'])->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'friend_id']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('friendships');
    }
};
