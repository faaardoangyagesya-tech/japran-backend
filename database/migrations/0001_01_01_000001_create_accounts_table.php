<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 7)->unique();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('name', 255);
            $table->decimal('price', 12, 2);
            $table->enum('stock_status', ['available', 'sold'])->default('available');
            $table->integer('sold_count')->default(0);
            $table->timestamps();

            $table->index('code');
            $table->index('stock_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
