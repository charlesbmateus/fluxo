<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id'       => User::factory(),
            'content'         => $this->faker->sentence(12),
            'read_at'         => $this->faker->boolean(60)
                ? now()->subMinutes(rand(1, 120))
                : null,
        ];
    }
}
