<?php

namespace App\Events;

use App\Models\Estimation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StartEstimationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Estimation $estimation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Estimation $estimation)
    {
        $this->estimation = $estimation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('game-' . $this->estimation->game->hash_id);
    }
}
