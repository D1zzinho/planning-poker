<?php

namespace App\Events;

use App\Models\Vote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Vote $vote;
    public bool $update;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Vote $vote, bool $update = false)
    {
        $this->vote = $vote;
        $this->update = $update;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PresenceChannel|array
     */
    public function broadcastOn(): Channel|PresenceChannel|array
    {
        return new PresenceChannel('game-' . $this->vote->estimation->game->hash_id);
    }
}
