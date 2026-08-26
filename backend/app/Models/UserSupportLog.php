<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSupportLog extends Model
{
    public const VERIFICATION_RESENT = 'verification_resent';
    public const PASSWORD_RESET_SENT = 'password_reset_sent';
    public const SESSIONS_REVOKED = 'sessions_revoked';
    public const ACCOUNT_UNBLOCKED = 'account_unblocked';

    protected $fillable = [
        'user_id',
        'admin_id',
        'action',
        'reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
