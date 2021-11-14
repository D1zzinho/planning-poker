<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimation_id',
        'points'
    ];

    protected $with = [
        'user',
        'estimation'
    ];

    /**
     * Overrides method in order to let model know, that it uses composite primary key.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query): \Illuminate\Database\Eloquent\Builder
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estimation(): BelongsTo
    {
        return $this->belongsTo(Estimation::class);
    }
}
