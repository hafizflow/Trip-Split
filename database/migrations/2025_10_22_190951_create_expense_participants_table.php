<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expense_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('split_amount', 10, 2);
            $table->timestamps();

            $table->unique(['expense_id', 'user_id']);
            $table->index(['expense_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('expense_participants');
    }
};
