<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 *
 * Disesuaikan dengan skema MediSlot:
 * - Kolom nama   : full_name  (bukan name)
 * - Kolom sandi  : password_hash (bukan password)
 * - Primary key  : UUID (HasUuids)
 */
class UserFactory extends Factory
{
    /** Password default yang di-cache antar pembuatan */
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'full_name'         => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password_hash'     => static::$password ??= Hash::make('password'),
            'role'              => 'user',
            'remember_token'    => Str::random(10),
        ];
    }

    /** State untuk email belum diverifikasi */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
