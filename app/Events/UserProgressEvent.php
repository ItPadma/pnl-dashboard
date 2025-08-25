<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserProgressEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $user;

    /**
     * Create a new event instance.
     */
    public function __construct(public $notifType, public $procName, public $progress, public $message, public $userId)
    {
        $this->user = User::find($userId);
        if (!$this->user) {
            throw new \Exception("User not found with ID: {$userId}");
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.User.Progress." . $this->user->id),
        ];
    }

    public function broadcastWith(): array
    {
        return array(
            'username' => $this->user->name,
            'ntype' => $this->notifType,
            'procname' => $this->procName,
            'progress' => $this->progress,
            'timestamp' => now()->toDateTimeString(),
            'message' => $this->message
        );
    }

    public function broadcastAs(): string
    {
        return 'user.proc.progress';
    }
}
