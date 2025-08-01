<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $notifType, public $notifTitle, public $notifMessage, public User $user)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.User." . $this->user->id),
        ];
    }

    public function broadcastWith(): array
    {
        return array(
            'username' => $this->user->name,
            'ntype' => $this->notifType,
            'title' => $this->notifTitle,
            'message' => $this->notifMessage,
        );
    }

    public function broadcastAs(): string
    {
        return 'user.notification';
    }
}
