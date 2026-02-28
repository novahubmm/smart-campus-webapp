<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InboxMessageReply extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'inbox_message_id',
        'sender_type',
        'sender_id',
        'body',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function inboxMessage(): BelongsTo
    {
        return $this->belongsTo(InboxMessage::class);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }
}
