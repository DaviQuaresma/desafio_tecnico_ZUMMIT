<?php

namespace Database\Seeders;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class TravelOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuários de teste
        $users = User::factory(5)->create();

        // Criar pedidos de viagem para cada usuário
        foreach ($users as $user) {
            // Pedidos solicitados
            TravelOrder::factory(3)
                ->forUser($user)
                ->requested()
                ->create();

            // Pedidos aprovados
            TravelOrder::factory(2)
                ->forUser($user)
                ->approved()
                ->create();

            // Pedidos cancelados
            TravelOrder::factory(1)
                ->forUser($user)
                ->canceled()
                ->create();
        }
    }
}
