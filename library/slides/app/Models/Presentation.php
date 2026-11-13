<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presentation extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'source_slug', 'title', 'slide_count'];

    public function publications(): HasMany
    {
        return $this->hasMany(ExternalPublication::class, 'slide_id');
    }
}
