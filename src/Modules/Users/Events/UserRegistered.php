<?php

namespace Modules\Users\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Interfaces\OtpEventInterface;
use Modules\Users\Models\User;

class UserRegistered implements OtpEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;


    public function __construct(User $user)
    {
        $this->user = $user;
    }
}