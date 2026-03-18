<?php

namespace Database\Factories;

use App\Enums\TravelOrderStatus;
use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelOrder>
 */
class TravelOrderFactory extends Factory
{
    protected $model = TravelOrder::class;

    public function definition(): array
    {
        $departureDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $returnDate = $this->faker->dateTimeBetween($departureDate, '+4 months');

        return [
            'user_id' => User::factory(),
            'destination' => $this->faker->city() . ', ' . $this->faker->country(),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'status' => TravelOrderStatus::REQUESTED,
        ];
    }

    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelOrderStatus::REQUESTED,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelOrderStatus::APPROVED,
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelOrderStatus::CANCELED,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function toDestination(string $destination): static
    {
        return $this->state(fn (array $attributes) => [
            'destination' => $destination,
        ]);
    }
}
