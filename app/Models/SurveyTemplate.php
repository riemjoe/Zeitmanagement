<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyTemplate extends Model
{
    protected $fillable = [
        'name', 'description', 'good_threshold', 'bad_threshold',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(SurveySection::class)->orderBy('position');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('position');
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }
}
