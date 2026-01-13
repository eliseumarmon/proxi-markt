<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
        CREATE TRIGGER actualizar_stock
        AFTER UPDATE ON compraventas
        FOR EACH ROW
        BEGIN
            UPDATE productos
            SET stock = stock - NEW.cantidad_total
            WHERE id = NEW.id_producto;
        END;
        ");

        DB::unprepared("
        CREATE TRIGGER actualizar_estado_producto
        AFTER UPDATE ON productos
        FOR EACH ROW
        BEGIN
            IF NEW.stock <= 0 THEN
                UPDATE productos
                SET estado = 'agotado'
                WHERE id = NEW.id;
            ELSE
                UPDATE productos
                SET estado = 'disponible'
                WHERE id = NEW.id;
            END IF;
        END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS actualizar_stock_reserva");
        DB::unprepared("DROP TRIGGER IF EXISTS actualizar_estado_producto");
    }
};
