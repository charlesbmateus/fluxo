<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        Conversation::all()->each(function (Conversation $conversation) {

            $senders = [
                $conversation->client_id,
                $conversation->provider_id,
            ];

            $lastSender = null;

            foreach (range(1, rand(5, 15)) as $i) {

                // Alternar sender
                $sender = $lastSender === $senders[0]
                    ? $senders[1]
                    : $senders[0];

                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id'       => $sender,
                ]);

                $lastSender = $sender;
            }
        });
    }
}
