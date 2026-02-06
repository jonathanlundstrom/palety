<?php

use App\Models\Pallet;
use App\Models\Recipient;
use App\Models\Transport;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('parcels', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignIdFor(Recipient::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignIdFor(Pallet::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->foreignIdFor(Transport::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('type');
            $table->float('weight');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('parcels');
    }
};
