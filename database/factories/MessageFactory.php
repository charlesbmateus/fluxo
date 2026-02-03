<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        $conversation = Conversation::factory()->create();

        return [
            'conversation_id' => $conversation->id,
            'sender_id'       => $conversation->client_id,
            'content'         => $this->faker->sentence(),
            'read_at'         => null,
        ];
    }

    /**
     * Message marked as read
     */
    public function read(): static
    {
        return $this->state(fn () => [
            'read_at' => now(),
        ]);
    }
}
