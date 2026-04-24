<?php

use App\Models\Content;
use App\Models\Pallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('content_pallet', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Content::class)->constrained('contents')->cascadeOnDelete();
            $table->foreignIdFor(Pallet::class)->constrained('pallets')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('content_pallet');
    }
};
