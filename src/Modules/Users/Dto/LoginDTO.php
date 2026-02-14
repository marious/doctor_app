<?php

namespace Modules\Users\Dto;

readonly class LoginDTO
{
    public function __construct(
        public string  $phoneOrEmail,
        public string  $password,
        public ?bool   $remember = false,
        public ?string $deviceName = null
    )
    {
    }

    public static function fromRequest($request): self
    {
        return new self(
            phoneOrEmail: $request->phone_or_email,
            password: $request->password,
            remember: $request->remember,
            deviceName: $request->device_name,
        );
    }
}