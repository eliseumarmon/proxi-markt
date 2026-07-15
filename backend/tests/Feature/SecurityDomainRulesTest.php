<?php

namespace Tests\Feature;

use App\Models\CompraVenta;
use App\Models\PuntoEntrega;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityDomainRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    public function test_user_cannot_update_another_users_product(): void
    {
        $owner = $this->createUser('owner@example.test');
        $otherUser = $this->createUser('other@example.test');
        $point = $this->createPoint($owner);
        $productId = $this->createProduct($owner, $point, ['stock_real' => 10]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/productos/{$productId}", []);

        $response->assertForbidden();
    }

    public function test_purchase_uses_product_seller_and_point_instead_of_client_payload(): void
    {
        $seller = $this->createUser('seller@example.test');
        $buyer = $this->createUser('buyer@example.test');
        $spoofedSeller = $this->createUser('spoofed@example.test');
        $sellerPoint = $this->createPoint($seller);
        $spoofedPoint = $this->createPoint($spoofedSeller);
        $productId = $this->createProduct($seller, $sellerPoint, ['stock_real' => 10]);

        Sanctum::actingAs($buyer);

        $response = $this->postJson("/api/compraventa/{$productId}", [
            'id_vendedor' => $spoofedSeller->id,
            'id_punto' => $spoofedPoint->id,
            'fecha_prevista' => '2026-08-01',
            'cantidad' => 2,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('compraventas', [
            'id_comprador' => $buyer->id,
            'id_vendedor' => $seller->id,
            'id_producto' => $productId,
            'id_punto' => $sellerPoint->id,
            'cantidad' => 2,
        ]);

        $this->assertDatabaseHas('productos', [
            'id' => $productId,
            'stock_reserva' => 2,
        ]);
    }

    public function test_purchase_can_only_be_rated_once_after_completion(): void
    {
        $seller = $this->createUser('seller@example.test');
        $buyer = $this->createUser('buyer@example.test');
        $point = $this->createPoint($seller);
        $productId = $this->createProduct($seller, $point);
        $purchase = CompraVenta::create([
            'id_comprador' => $buyer->id,
            'id_vendedor' => $seller->id,
            'id_producto' => $productId,
            'id_punto' => $point->id,
            'cantidad' => 1,
            'precio' => 5.50,
            'fecha_prevista' => '2026-08-01',
            'estado' => 'pendiente',
        ]);

        Sanctum::actingAs($buyer);

        $this->postJson("/api/valoraciones/{$purchase->id}", [
            'valoracion' => 5,
            'comentario' => 'Todo perfecto.',
        ])->assertUnprocessable();

        $purchase->update(['estado' => 'completado']);

        $this->postJson("/api/valoraciones/{$purchase->id}", [
            'valoracion' => 5,
            'comentario' => 'Todo perfecto.',
        ])->assertOk();

        $this->postJson("/api/valoraciones/{$purchase->id}", [
            'valoracion' => 4,
            'comentario' => 'Duplicada.',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('valoraciones', 1);
    }

    private function createSchema(): void
    {
        Schema::dropIfExists('valoraciones');
        Schema::dropIfExists('compraventas');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('puntos_entrega');
        Schema::dropIfExists('usuarios');

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_usuario');
            $table->string('email')->unique();
            $table->string('contrasenya')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('puntos_entrega', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario');
            $table->decimal('longitud', 10, 7);
            $table->decimal('latitud', 10, 7);
            $table->string('nombre_punto');
            $table->string('direccion_punto');
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_categoria')->nullable();
            $table->foreignId('id_usuario');
            $table->foreignId('id_puntoentrega');
            $table->string('nombre_producto');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 8, 2);
            $table->integer('stock_total')->default(0);
            $table->integer('stock_reserva')->default(0);
            $table->integer('stock_real')->default(0);
            $table->string('imagen')->nullable();
            $table->string('estado')->default('disponible');
            $table->timestamps();
        });

        Schema::create('compraventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto');
            $table->foreignId('id_comprador');
            $table->foreignId('id_vendedor');
            $table->foreignId('id_punto');
            $table->integer('cantidad');
            $table->string('estado')->default('pendiente');
            $table->decimal('precio', 8, 2);
            $table->date('fecha_prevista');
            $table->timestamps();
        });

        Schema::create('valoraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta');
            $table->foreignId('id_valorador');
            $table->foreignId('id_valorado');
            $table->tinyInteger('valoracion');
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->unique(['id_venta', 'id_valorador']);
        });
    }

    private function createUser(string $email): User
    {
        return User::create([
            'nombre_usuario' => strstr($email, '@', true),
            'email' => $email,
            'contrasenya' => 'password',
        ]);
    }

    private function createPoint(User $user): PuntoEntrega
    {
        return PuntoEntrega::create([
            'id_usuario' => $user->id,
            'longitud' => -0.3763,
            'latitud' => 39.4699,
            'nombre_punto' => 'Mercado central',
            'direccion_punto' => 'Valencia',
        ]);
    }

    private function createProduct(User $user, PuntoEntrega $point, array $overrides = []): int
    {
        return DB::table('productos')->insertGetId(array_merge([
            'id_categoria' => 1,
            'id_usuario' => $user->id,
            'id_puntoentrega' => $point->id,
            'nombre_producto' => 'Tomates',
            'descripcion' => 'Tomates de temporada',
            'precio' => 5.50,
            'stock_total' => 10,
            'stock_reserva' => 0,
            'stock_real' => 10,
            'imagen' => 'productos/default.png',
            'estado' => 'disponible',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
