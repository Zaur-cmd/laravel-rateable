<?php

namespace willvincent\Rateable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Rating extends Model
{
    protected $fillable = ['rating', 'rateable_type', 'rateable_id', 'rater_type', 'rater_id', 'comment'];

    public function rateable()
    {
        return $this->morphTo();
    }

    public function rater()
    {
        return $this->morphTo();
    }
}
