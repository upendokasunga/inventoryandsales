<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model, null, $model->toArray());
    }

    public function updated(Model $model): void
    {
        if (!$model->isDirty()) {
            return;
        }

        $old = array_intersect_key(
            $model->getOriginal(),
            $model->getDirty()
        );

        $new = $model->getDirty();

        $this->log('updated', $model, $old, $new);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, $model->toArray(), null);
    }

    public function restored(Model $model): void
    {
        $this->log('restored', $model, null, $model->toArray());
    }

    public function forceDeleted(Model $model): void
    {
        $this->log('force_deleted', $model, $model->toArray(), null);
    }

    protected function log(string $event, Model $model, ?array $old, ?array $new): void
    {
        try {
            $user = Auth::user();

            AuditLog::create([
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'user_id' => $user?->id,
                'event' => $event,
                'old_values' => $old ? json_encode($old) : null,
                'new_values' => $new ? json_encode($new) : null,
                'ip_address' => app()->runningInConsole() ? 'console' : Request::ip(),
                'user_agent' => app()->runningInConsole() ? 'CLI' : Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
