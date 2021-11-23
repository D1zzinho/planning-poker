<?php

namespace App\Models;

use http\Exception\InvalidArgumentException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['status'];

    /**
     * Game status.
     */
    protected const GAME_STATUS = [
        'active',
        'closed'
    ];

    /**
     * Overrides magic method in order to creating random hash string automatically.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if (empty($this->hash_id)) {
            $this->hash_id = Str::random(40);
        }

        if (empty($this->status)) {
            $this->status = self::getGameStatus(0);
        }

        return parent::save($options);
    }

    /**
     * Get game status.
     *
     * @param int $key
     * @return string
     */
    public static function getGameStatus(int $key): string
    {
        if (!array_key_exists($key, self::GAME_STATUS)) {
            throw new InvalidArgumentException(
                sprintf('Game status [%c] not found.', $key)
            );
        }

        return self::GAME_STATUS[$key];
    }

    /**
     * Get available game statuses, return its keys or values.
     *
     * @param bool $keys If true, method returns type keys, otherwise type values.
     * @return array User type keys or values.
     */
    public static function getAvailableGameStatuses(bool $keys = false): array
    {
        return ($keys) ? array_keys(self::GAME_STATUS) : array_values(self::GAME_STATUS);
    }

    /**
     * Game belongs to User relation.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Game can have many (has many) estimations relation.
     *
     * @return HasMany
     */
    public function estimations(): HasMany
    {
        return $this->hasMany(Estimation::class);
    }
}
