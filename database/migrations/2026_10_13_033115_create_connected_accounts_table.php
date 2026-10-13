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
        Schema::create('connected_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // 'microsoft' o 'google'
            $table->string('provider_id'); // ID devuelto por el proveedor
            $table->text('token'); // Token cifrado
            $table->text('refresh_token')->nullable(); // Refresh token cifrado
            $table->timestamp('expires_at')->nullable();
            $table->jsonb('scopes')->nullable(); // Soporte nativo JSONB en PostgreSQL
            $table->timestamps();

            // Evita duplicar el mismo proveedor por usuario
            $table->unique(['user_id', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connected_accounts');
    }
};