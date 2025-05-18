<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestSetting extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'contest_channel', 'contest_setting_id', 'channel_id');
    }

    public function prizes()
    {
        return $this->hasMany(Prize::class, 'contest_setting_id');
    }
}
