<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalPublication extends Model
{
    use HasUuids;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published_at' => 'immutable_datetime', 'last_synced_at' => 'immutable_datetime'];
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class, 'slide_id');
    }
}
