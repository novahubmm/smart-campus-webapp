<?php

namespace App\DTOs\Auth;

use Illuminate\Contracts\Support\Arrayable;

class LoginData implements Arrayable
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $device_name = null,
    ) {}

    public static function from(array $data): self
    {
        return new self(
            email: $data['email'] ?? $data['login'],
            password: $data['password'],
            device_name: $data['device_name'] ?? 'web',
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => $this->device_name,
        ];
    }
}
