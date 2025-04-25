<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ViagemFactory extends Factory
{
    public function definition()
    {
        $dataIda = fake()->dateTimeBetween('+1 days', '+1 month');
        $dataVolta = fake()->dateTimeBetween($dataIda, '+2 months');

        return [
            'user_id' => User::factory(),
            'nome_solicitante' => fake()->name(),
            'destino' => fake()->city(),
            'data_ida' => $dataIda->format('Y-m-d'),
            'data_volta' => $dataVolta->format('Y-m-d'),
            'status' => fake()->randomElement(['solicitado', 'aprovado', 'cancelado']),
        ];
    }
}
