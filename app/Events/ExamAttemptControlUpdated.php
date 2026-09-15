<?php

namespace App\Events;

use App\Models\ExamAttempt;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamAttemptControlUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $exam_id;
    public int $attempt_id;
    public int $student_id;
    public string $control_type;  // 'lock' | 'unlock' | 'extra_time' | 'force_submit'
    public string $status;
    public bool $is_locked;
    public ?string $deadline_at;
    public ?string $locked_at;
    protected ?string $lock_reason;

    public function __construct(ExamAttempt $attempt, string $controlType)
    {
        $this->exam_id = $attempt->exam_id;
        $this->attempt_id = $attempt->id;
        $this->student_id = $attempt->student_id;
        $this->control_type = $controlType;
        $this->status = $attempt->status;
        $this->is_locked = (bool) $attempt->locked_at;
        $this->deadline_at = $attempt->deadline_at?->toIso8601String();
        $this->locked_at = $attempt->locked_at?->toIso8601String();
        $this->lock_reason = $attempt->lock_reason;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('exam.' . $this->exam_id . '.monitoring'),
            new PrivateChannel('attempt.' . $this->attempt_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attempt.control.updated';
    }
}
