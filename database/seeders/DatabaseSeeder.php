<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::factory()->create([
            'name' => 'Harry',
            'email' => 'harry@example.com',
            'password' => bcrypt('password'),
            'is_admin' => true
        ]);

        // Create Standard User
        $user = User::factory()->create([
            'name' => 'Jon',
            'email' => 'jon@example.com',
            'password' => bcrypt('password')
        ]);

        // Create Additional Users
        User::factory(10)->create();

        // Create Groups and Attach Users
        for ($i = 0; $i < 5; $i++) {
            $ownerId = User::inRandomOrder()->first()->id;
            $group = Group::factory()->create([
                'owner_id' => $ownerId,
            ]);

            $users = User::inRandomOrder()->limit(rand(2, 5))->pluck('id')->toArray();
            $group->users()->attach(array_unique([$ownerId, ...$users]));
        }

        // Ensure users exist before creating messages
        if (User::count() > 1) {
            Message::factory(1000)->create();
        }

        // Process Messages for Conversations
        $messages = Message::whereNull('group_id')
            ->whereNotNull('receiver_id')
            ->orderBy('created_at')
            ->get();

        $conversations = $messages->groupBy(function ($message) {
            return collect([$message->sender_id, $message->receiver_id])
                ->sort()
                ->implode('_');
        })->map(function ($groupedMessages) {
            return [
                'user_id1' => $groupedMessages->first()->sender_id,
                'user_id2' => $groupedMessages->first()->receiver_id,
                'last_message_id' => $groupedMessages->last()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        })->values();

        // Insert Conversations
        Conversation::insertOrIgnore($conversations->toArray());
    }
}
