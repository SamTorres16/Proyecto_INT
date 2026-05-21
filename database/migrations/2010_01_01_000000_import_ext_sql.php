<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Solo importar si la tabla principal 'usuario' no existe (base de datos vacía)
        if (!Schema::hasTable('usuario')) {
            $path = base_path('ext.sql');
            if (File::exists($path)) {
                $sql = File::get($path);
                DB::unprepared($sql);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hacer nada en down
    }
};
