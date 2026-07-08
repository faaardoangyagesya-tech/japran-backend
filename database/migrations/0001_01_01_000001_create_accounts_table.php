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
            $table->unsignedBigInteger('game_id');
            $table->string('name', 255);
            $table->decimal('price', 12, 2);
            $table->string('stock_status', 20)->default('available');
            $table->integer('sold_count')->default(0);
            $table->timestamps();

            $table->index('code');
            $table->index('stock_status');
            $table->index('game_id');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
        });
        Schema::dropIfExists('accounts');
    }
};
