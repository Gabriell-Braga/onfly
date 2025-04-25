<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Viagem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViagemTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // cria usuários
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create();
    }

    public function test_usuario_pode_se_registrar()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Novo Usuário',
            'email' => 'novo@teste.com',
            'password' => '123456',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'novo@teste.com']);
    }

    public function test_usuario_pode_fazer_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
    }

    public function test_usuario_pode_criar_viagem()
    {
        $token = auth('api')->login($this->user);

        $response = $this->postJson('/api/viagens', [
            'nome_solicitante' => 'João',
            'destino' => 'BH',
            'data_ida' => '2025-06-01',
            'data_volta' => '2025-06-10'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('viagems', ['destino' => 'BH']);
    }

    public function test_usuario_lista_apenas_suas_viagens()
    {
        Viagem::factory()->create(['user_id' => $this->user->id]);
        Viagem::factory()->create(['user_id' => $this->admin->id]);

        $token = auth('api')->login($this->user);

        $response = $this->getJson('/api/viagens', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    public function test_admin_lista_todas_as_viagens()
    {
        Viagem::factory()->create(['user_id' => $this->user->id]);
        Viagem::factory()->create(['user_id' => $this->admin->id]);

        $token = auth('api')->login($this->admin);

        $response = $this->getJson('/api/viagens', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
    }

    public function test_apenas_admin_pode_alterar_status()
    {
        $viagem = Viagem::factory()->create(['user_id' => $this->user->id]);

        // tentativa com usuário comum
        $tokenUser = auth('api')->login($this->user);
        $resUser = $this->patchJson("/api/viagens/{$viagem->id}/status", [
            'status' => 'aprovado'
        ], [
            'Authorization' => 'Bearer ' . $tokenUser
        ]);
        $resUser->assertStatus(403);

        // com admin
        $tokenAdmin = auth('api')->login($this->admin);
        $resAdmin = $this->patchJson("/api/viagens/{$viagem->id}/status", [
            'status' => 'aprovado'
        ], [
            'Authorization' => 'Bearer ' . $tokenAdmin
        ]);
        $resAdmin->assertStatus(200);
    }

    public function test_usuario_nao_acessa_viagem_de_outro_usuario()
    {
        $viagemDeOutro = Viagem::factory()->create(); // outro user
        $token = auth('api')->login($this->user);

        $res = $this->getJson("/api/viagens/{$viagemDeOutro->id}", [
            'Authorization' => 'Bearer ' . $token
        ]);

        $res->assertStatus(404);
    }

    public function test_admin_acessa_viagem_de_qualquer_usuario()
    {
        $viagem = Viagem::factory()->create(['user_id' => $this->user->id]);
        $token = auth('api')->login($this->admin);

        $res = $this->getJson("/api/viagens/{$viagem->id}", [
            'Authorization' => 'Bearer ' . $token
        ]);

        $res->assertStatus(200)
            ->assertJsonFragment(['id' => $viagem->id]);
    }

    public function test_filtro_por_status_funciona()
    {
        Viagem::factory()->create(['user_id' => $this->user->id, 'status' => 'aprovado']);
        Viagem::factory()->create(['user_id' => $this->user->id, 'status' => 'cancelado']);

        $token = auth('api')->login($this->user);

        $res = $this->getJson('/api/viagens?status=aprovado', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $res->assertStatus(200);
        $this->assertCount(1, $res->json());
    }

    public function test_filtro_por_destino_funciona()
    {
        Viagem::factory()->create(['user_id' => $this->user->id, 'destino' => 'São Paulo']);
        Viagem::factory()->create(['user_id' => $this->user->id, 'destino' => 'Rio de Janeiro']);

        $token = auth('api')->login($this->user);

        $res = $this->getJson('/api/viagens?destino=São', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $res->assertStatus(200);
        $this->assertCount(1, $res->json());
    }
}
