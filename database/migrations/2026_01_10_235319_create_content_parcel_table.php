<?php

use App\Models\Content;
use App\Models\Parcel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('content_parcel', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Content::class)->constrained('contents')->cascadeOnDelete();
            $table->foreignIdFor(Parcel::class)->constrained('parcels')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('content_parcel');
    }
};
