<?php

use App\Enumerables\PalletStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('pallets', function (Blueprint $table) {
            $table->string('status')->default(PalletStatus::DRAFT->name);
            $table->dropColumn([
                'category',
                'label_en',
                'label_ua',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('pallets', function (Blueprint $table) {
            $table->dropColumn(['status']);
            $table->string('category')->nullable();
            $table->string('label_en')->nullable();
            $table->string('label_ua')->nullable();
        });
    }
};
