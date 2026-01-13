<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use App\Models\Recipient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Recipient::class, 'parent_id')
                ->nullable()
                ->constrained();
            $table->string('name');
            $table->string('type')->default(RecipientType::ORGANISATION->name);
            $table->string('reference')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number');
            $table->string('organisation_number')->nullable();
            $table->string('delivery_type')->default(DeliveryType::NOVA_POSHTA_DELIVERY->name);
            $table->string('address')->nullable();
            $table->string('zipcode')->nullable();
            $table->integer('nova_poshta_id')->nullable();
            $table->string('city')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('recipients');
    }
};
