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
        // 1. Modyfikacja tabeli users: zmiana id na UUID
        Schema::table('users', function (Blueprint $table) {
            // Najpierw zmień id na BIGINT bez AUTO_INCREMENT (krok pośredni)
            $table->bigInteger('id')->autoIncrement(false)->change();
            // Usuń klucz główny
            $table->dropPrimary('users_id_primary');
            // Zmień typ na UUID
            $table->uuid('id')->change();
            // Ustaw nowy klucz główny
            $table->primary('id');
        });

        // 2. Modyfikacja tabeli sessions: zmiana user_id na UUID
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->uuid('user_id')->nullable()->index()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 3. Utworzenie tabeli language_tests
        Schema::create('language_tests', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('language');
            $table->string('level')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('tested_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Usuń tabelę language_tests
        Schema::dropIfExists('language_tests');

        // 2. Przywróć sessions do stanu pierwotnego
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('user_id')->nullable()->index()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 3. Przywróć users do stanu pierwotnego
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary('users_id_primary');
            $table->bigIncrements('id')->change(); // Przywróć autoinkrementowane id
            $table->primary('id');
        });
    }
};
