<?php

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

if (!function_exists('activity')) {
    function activity(): ActivityLogger
    {
        return app(ActivityLogger::class);
    }
}

class ActivityLogger
{
    protected ?Model $subject = null;
    protected array $properties = [];

    public function performedOn(Model $model): static
    {
        $this->subject = $model;
        return $this;
    }

    public function withProperties(array $properties): static
    {
        $this->properties = $properties;
        return $this;
    }

    public function log(string $description): void
    {
        try {
            $data = [
                'auditable_type' => $this->subject ? get_class($this->subject) : null,
                'auditable_id' => $this->subject?->getKey(),
                'user_id' => Auth::id(),
                'event' => $description,
                'old_values' => null,
                'new_values' => $this->properties,
                'ip_address' => app()->runningInConsole() ? 'console' : Request::ip(),
                'user_agent' => app()->runningInConsole() ? 'CLI' : Request::userAgent(),
            ];

            AuditLog::create($data);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
