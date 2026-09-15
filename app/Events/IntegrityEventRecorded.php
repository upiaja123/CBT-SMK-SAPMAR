<?php

namespace App\Events;

use App\Models\IntegrityEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IntegrityEventRecorded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $exam_id;
    public $attempt_id;
    public $student_id;
    public $event_type;
    public $occurred_at;
    public $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(IntegrityEvent $event)
    {
        $this->exam_id = $event->attempt->exam_id;
        $this->attempt_id = $event->exam_attempt_id;
        $this->student_id = $event->student_id;
        $this->event_type = $event->event_type;
        $this->occurred_at = $event->occurred_at ? $event->occurred_at->toIso8601String() : null;
        $this->metadata = $event->metadata;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('exam.' . $this->exam_id . '.monitoring'),
        ];
    }
}
