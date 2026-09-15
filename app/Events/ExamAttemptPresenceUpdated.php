<?php

namespace App\Events;

use App\Models\ExamAttempt;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamAttemptPresenceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $exam_id;
    public $attempt_id;
    public $student_id;
    public $student_name;
    public $connection_status;
    public $last_seen_at;
    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct(ExamAttempt $attempt)
    {
        // Avoid passing the entire model to minimize payload size and avoid data leakage
        $this->exam_id = $attempt->exam_id;
        $this->attempt_id = $attempt->id;
        $this->student_id = $attempt->student_id;
        $this->student_name = $attempt->student->user->name ?? 'Unknown';
        $this->connection_status = $attempt->connection_status; // Dynamic accessor
        $this->last_seen_at = $attempt->last_seen_at ? $attempt->last_seen_at->toIso8601String() : null;
        $this->status = $attempt->status;
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
