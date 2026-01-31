<?php

use App\Models\Recipient;
use App\Models\User;
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
                ->constrained()
                ->nullOnDelete();
            $table->foreignIdFor(User::class)
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('type');
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
