<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'avatar',
        'name',
        'email',
        'password',
        'is_admin'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationship with Groups (Many-to-Many)
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    // Relationship with Owned Groups (One-to-Many)
    public function ownedGroups()
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    // Relationship with Sent Messages (One-to-Many)
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Relationship with Received Messages (One-to-Many)
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public static function getUsersExceptUser(User $User)
    {
        $userId = $User->id;
        $query = User::select(['users.*','messages.message as last_message','messages.created_at as last_message_date'])
            ->where('users.id', '!=', $userId)
            ->when(!$User->is_admin, function ($query) {
                $query->whereNull('users.blocked_at');
            })
            ->leftJoin('conversations',function ($join) use ($userId) {
                $join->on('conversations.user_id1', '=', 'users.id')
                    ->where('conversations.user_id2', '=', $userId)
                    ->orWhere(function ($query) use ($userId) {
                        $query->on('conversations.user_id2', '=', 'users.id')
                            ->where('conversations.user_id1', '=', $userId);
                    });
            })
            ->leftJoin('messages', 'messages.id', '=', 'conversations.last_message_id')
            ->orderByRaw('IFNULL(users.blocked_at,1)')
            ->orderBy('messages.created_at', 'desc')
            ->orderBy('users.name')
        ;

        return $query->get();
    }

    public function toConversationArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_group' => false,
            'is_user' => true,
            'is_admin' => (bool) $this->is_admin,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'blocked_at' => $this->blocked_at,  
            'last_message' => $this->last_message,  
            'last_message_date' => $this->last_message_date,      
        ];
    }
}
