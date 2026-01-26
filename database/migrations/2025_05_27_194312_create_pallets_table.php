<?php

use App\Enumerables\PalletType;
use App\Models\Recipient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('pallets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Recipient::class)
                ->nullable()
                ->constrained();
            $table->string('type')->default(PalletType::CALCULATED->name);
            $table->string('label_en')->nullable();
            $table->string('label_ua')->nullable();
            $table->float('weight')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('pallets');
    }
};
