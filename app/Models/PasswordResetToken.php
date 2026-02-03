<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';

    protected $primaryKey = 'email';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'otp_code',
        'dev_otp_plain',
        'otp_expires_at',
        'otp_resent_at',
        'created_at',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'otp_resent_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
