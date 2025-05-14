<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'telegram_id',
        'name',
        'invite_link',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'channel_members', 'channel_id', 'user_id');
    }

    public function contests()
    {
        return $this->belongsToMany(ContestSetting::class, 'contest_channel', 'channel_id', 'contest_setting_id');
    }
}
