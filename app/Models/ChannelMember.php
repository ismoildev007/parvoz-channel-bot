<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelMember extends Pivot
{
    protected $table = 'channel_members';

    protected $fillable = [
        'user_id',
        'channel_id',
    ];
}
