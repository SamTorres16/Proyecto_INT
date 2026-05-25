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
        Schema::table('Act_extraesc', function (Blueprint $table) {
            if (!Schema::hasColumn('Act_extraesc', 'inscripcion_abierta')) {
                $table->tinyInteger('inscripcion_abierta')->default(1)->after('cupo_femenino');
                $table->tinyInteger('parcial1_cerrado')->default(0)->after('inscripcion_abierta');
                $table->tinyInteger('parcial2_cerrado')->default(0)->after('parcial1_cerrado');
                $table->tinyInteger('parcial3_cerrado')->default(0)->after('parcial2_cerrado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Act_extraesc', function (Blueprint $table) {
            $table->dropColumn(['inscripcion_abierta', 'parcial1_cerrado', 'parcial2_cerrado', 'parcial3_cerrado']);
        });
    }
};
