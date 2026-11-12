<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLogger
{
    /**
     * Record an owner or staff action for the activity log.
     *
     * @param  array<string, array{old?: mixed, new?: mixed}>  $changes
     */
    public function log(
        string $action,
        Model $subject,
        string $label,
        string $description,
        array $changes = [],
        ?int $branchId = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => auth()->id(),
            'branch_id' => $branchId,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'subject_label' => Str::limit($label, 150, ''),
            'description' => Str::limit($description, 255, ''),
            'changes' => $changes === [] ? null : $changes,
            'ip_address' => request()->ip(),
        ]);
    }
}
