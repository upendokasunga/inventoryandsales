<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLoginFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $email;
    public string $ipAddress;
    public ?string $userAgent;

    public function __construct(string $email, string $ipAddress, ?string $userAgent)
    {
        $this->email = $email;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
}
