<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalTrackingLog extends Model
{
    protected $table = 'approval_tracking_log';

    protected $fillable = [
        'approval_tracking_id',
        'level',
        'action',
        'user_id',
        'comments',
    ];

    public function tracking(): BelongsTo
    {
        return $this->belongsTo(ApprovalTracking::class, 'approval_tracking_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
