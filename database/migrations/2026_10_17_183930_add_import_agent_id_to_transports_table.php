<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add the import agent responsible for the transport. Deleting the
     * agent's user account keeps the transport, without an agent.
     */
    public function up(): void {
        Schema::table('transports', function (Blueprint $table): void {
            $table->foreignId('import_agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('transports', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('import_agent_id');
        });
    }
};
