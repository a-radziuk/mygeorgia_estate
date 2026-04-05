<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Maps Korter object/layout ids to stable public codes (e.g. QK742-019): two random letters,
 * three random digits, hyphen, three random digits — assigned once and stored.
 *
 * @property-read int $id
 * @property int|null $korter_object_id
 * @property int|null $korter_layout_id
 * @property string|null $public_code
 */
class KorterListingPublicCode extends Model
{
    protected $table = 'korter_listing_public_codes';

    protected $guarded = [];

    private const ALLOCATE_ATTEMPTS = 64;

    /**
     * Return existing code for this Korter id, or allocate a new random code and persist it.
     */
    public static function allocateOrGet(int $korterId, bool $isObject): string
    {
        return DB::transaction(function () use ($korterId, $isObject): string {
            $existing = static::query()
                ->when($isObject, fn ($q) => $q->where('korter_object_id', $korterId), fn ($q) => $q->where('korter_layout_id', $korterId))
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return self::ensurePublicCode($existing);
            }

            for ($attempt = 0; $attempt < self::ALLOCATE_ATTEMPTS; $attempt++) {
                $code = self::generateRandomPublicCode();
                $row = new self;
                if ($isObject) {
                    $row->korter_object_id = $korterId;
                } else {
                    $row->korter_layout_id = $korterId;
                }
                $row->public_code = $code;

                try {
                    $row->save();

                    return $code;
                } catch (UniqueConstraintViolationException) {
                    $lostRace = static::query()
                        ->when($isObject, fn ($q) => $q->where('korter_object_id', $korterId), fn ($q) => $q->where('korter_layout_id', $korterId))
                        ->first();

                    if ($lostRace !== null) {
                        return self::ensurePublicCode($lostRace);
                    }

                    // Duplicate random public_code — try another.
                }
            }

            throw new \RuntimeException('Could not allocate a unique Korter public code.');
        });
    }

    private static function ensurePublicCode(self $row): string
    {
        if ($row->public_code !== null && $row->public_code !== '') {
            return $row->public_code;
        }

        for ($attempt = 0; $attempt < self::ALLOCATE_ATTEMPTS; $attempt++) {
            $row->public_code = self::generateRandomPublicCode();
            try {
                $row->save();

                return $row->public_code;
            } catch (UniqueConstraintViolationException) {
                // Retry with a new random code.
            }
        }

        throw new \RuntimeException('Could not fill missing Korter public code.');
    }

    /**
     * Pattern: two letters (A–Z), three digits, hyphen, three digits — 9 characters, all random.
     */
    private static function generateRandomPublicCode(): string
    {
        $letters = chr(random_int(65, 90)).chr(random_int(65, 90));

        return sprintf(
            '%s%03d-%03d',
            $letters,
            random_int(0, 999),
            random_int(0, 999),
        );
    }
}
