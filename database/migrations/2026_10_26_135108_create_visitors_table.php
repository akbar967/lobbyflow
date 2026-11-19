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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nama_tamu
            $table->string('nama_tamu')->nullable();
            $table->string('instansi');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('host_tujuan')->nullable();
            $table->string('purpose'); // keperluan
            $table->string('keperluan')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('status')->default('INSIDE');
            $table->timestamp('check_in_at')->nullable()->useCurrent();
            $table->timestamp('waktu_checkin')->nullable()->useCurrent();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamp('waktu_checkout')->nullable();
            $table->string('durasi')->nullable();
            $table->string('id_card_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
