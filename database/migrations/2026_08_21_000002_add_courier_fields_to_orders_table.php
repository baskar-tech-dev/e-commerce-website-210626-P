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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('courier_id')->nullable()->after('courier_name');
            $table->string('courier_tracking_url', 500)->nullable()->after('tracking_number');
            $table->string('courier_contact_number', 50)->nullable()->after('courier_tracking_url');
            $table->string('courier_person_name', 100)->nullable()->after('courier_contact_number');

            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropColumn([
                'courier_id',
                'courier_tracking_url',
                'courier_contact_number',
                'courier_person_name',
            ]);
        });
    }
};
