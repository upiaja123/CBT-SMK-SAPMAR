<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\AuditLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'reason',
        'outcome',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Audit logs must not be updated or deleted by ordinary application logic.
     */
    public static bool $readonly = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function record(
        string $action,
        ?Model $target = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $outcome = 'success',
        ?string $reason = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $target?->getMorphClass(),
            'auditable_id' => $target?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reason' => $reason,
            'outcome' => $outcome,
        ]);
    }
}
