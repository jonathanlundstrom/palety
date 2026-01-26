<?php

use App\Enumerables\ParcelType;
use App\Models\Pallet;
use App\Models\Recipient;
use App\Models\Transport;
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
            $table->string('type');
            $table->string('label_en');
            $table->string('label_ua');
            $table->float('weight');
            $table->foreignIdFor(Recipient::class)
                ->nullable()
                ->constrained();
            $table->foreignIdFor(Pallet::class)
                ->nullable()
                ->constrained();
            $table->foreignIdFor(Transport::class)
                ->nullable()
                ->constrained();
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
