<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Cria um usuário automaticamente se não passar o id
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(2),
            'viagem_id' => null, // você pode ajustar depois para associar a uma viagem se quiser
            'sent_at' => now(),
            'read' => false,
        ];
    }
}
