<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['first_name', 'last_name', 'mentor_name', 'votes', 'contest_id'];

    public function contest()
    {
        return $this->belongsTo(ContestSetting::class, 'contest_id');
    }
}
