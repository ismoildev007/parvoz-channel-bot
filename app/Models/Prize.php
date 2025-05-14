<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    protected $fillable = [
        'contest_setting_id',
        'name',
        'position',
    ];

    public function contest()
    {
        return $this->belongsTo(ContestSetting::class, 'contest_setting_id');
    }
}
