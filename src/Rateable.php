<?php

namespace willvincent\Rateable;

use Illuminate\Support\Facades\Auth;

trait Rateable
{
    public function rate($value, $comment = null, $rater = null)
    {
        $rater = $rater ?: Auth::user();

        $rating = new Rating();
        $rating->rating = $value;
        $rating->comment = $comment;
        $rating->rater_id = $rater->getKey();
        $rating->rater_type = get_class($rater);

        $this->ratings()->save($rating);
    }

    public function rateOnce($value, $rater = null, $comment = null)
    {
        $rater = $rater ?: Auth::user();

        $existing = Rating::query()
            ->where('rateable_type', $this->getMorphClass())
            ->where('rateable_id', $this->getKey())
            ->where('rater_type', get_class($rater))
            ->where('rater_id', $rater->getKey())
            ->first();

        if ($existing) {
            $existing->rating = $value;
            $existing->comment = $comment;
            $existing->save();
        } else {
            $this->rate($value, $comment, $rater);
        }
    }

    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function sumRating()
    {
        return $this->ratings()->sum('rating');
    }

    public function timesRated()
    {
        return $this->ratings()->count();
    }

    public function ratersCount()
    {
        return $this->ratings()
            ->select('rater_type', 'rater_id')
            ->distinct()
            ->count();
    }

    public function raterAverageRating($rater = null)
    {
        $rater = $rater ?: Auth::user();

        return $this->ratings()
            ->where('rater_type', get_class($rater))
            ->where('rater_id', $rater->getKey())
            ->avg('rating');
    }

    public function ratingPercent($max = 5, bool $rounded = false)
    {
        $total = $this->sumRating();
        $count = $this->timesRated();

        if ($count * $max === 0) return 0;

        $percent = ($total / ($count * $max)) * 100;

        return $rounded ? ceil($percent) : $percent;
    }

    // Getters

    public function getAverageRatingAttribute()
    {
        return $this->averageRating();
    }

    public function getSumRatingAttribute()
    {
        return $this->sumRating();
    }

    public function getRaterAverageRatingAttribute()
    {
        return $this->raterAverageRating();
    }
}
