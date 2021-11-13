<?php

namespace App\Models;

use http\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estimation extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'task',
        'points',
        'status'
    ];

    protected $with = ['game'];

    /**
     * Estimation status.
     */
    protected const ESTIMATION_STATUS = [
        'open',
        'closed'
    ];

    /**
     * Get estimation status.
     *
     * @param int $key
     * @return string
     */
    public static function getEstimationStatus(int $key): string
    {
        if (!array_key_exists($key, self::ESTIMATION_STATUS)) {
            throw new InvalidArgumentException(
                sprintf('Estimation status [%c] not found.', $key)
            );
        }

        return self::ESTIMATION_STATUS[$key];
    }

    /**
     * Get available estimaion statuses, return its keys or values.
     *
     * @param bool $keys If true, method returns type keys, otherwise type values.
     * @return array User type keys or values.
     */
    public static function getAvailableEstimationStatuses(bool $keys = false): array
    {
        return ($keys) ? array_keys(self::ESTIMATION_STATUS) : array_values(self::ESTIMATION_STATUS);
    }

    /**
     * Estimation belongs to one game.
     *
     * @return BelongsTo
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
