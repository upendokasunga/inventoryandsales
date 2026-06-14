<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public function getAll(int $perPage = 50)
    {
        return AuditLog::with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function getByModel(string $modelType, ?int $modelId = null, int $perPage = 50)
    {
        $query = AuditLog::where('auditable_type', $modelType);

        if ($modelId) {
            $query->where('auditable_id', $modelId);
        }

        return $query->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function getByEvent(string $event, int $perPage = 50)
    {
        return AuditLog::where('event', $event)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function getByUser(int $userId, int $perPage = 50)
    {
        return AuditLog::where('user_id', $userId)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function search(string $query, int $perPage = 50)
    {
        return AuditLog::where(function ($q) use ($query) {
            $q->where('auditable_type', 'like', "%{$query}%")
              ->orWhere('event', 'like', "%{$query}%");
        })
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }
}
