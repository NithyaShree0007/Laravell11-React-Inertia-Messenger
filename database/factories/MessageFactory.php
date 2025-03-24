<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure at least two users exist
        if (User::count() < 2) {
            // Create extra users if necessary
            User::factory(2)->create();
        }

        // Select sender and receiver using inRandomOrder
        $senderId = User::inRandomOrder()->first()->id;
        $receiverId = User::where('id', '!=', $senderId)->inRandomOrder()->first()->id;

        $groupId = null;

        // Handle group messages
        if (Group::count() > 0 && $this->faker->boolean(50)) {
            $group = Group::inRandomOrder()->first();
            $groupId = $group->id;

            // Ensure sender is a member of the group
            $senderId = $group->users()->inRandomOrder()->first()->id;
            $receiverId = null; // Receiver is null in a group chat
        }

        return [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'group_id' => $groupId,
            'message' => $this->faker->realText(200),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
