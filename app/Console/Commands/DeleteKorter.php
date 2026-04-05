<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\KorterImportRun;
use App\Models\KorterImportState;
use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteKorter extends Command
{
    protected $signature = 'korter:delete
                            {--all : Delete every listing row, including entries without Korter IDs}
                            {--force : Run without confirmation}';

    protected $description = 'Remove Korter-imported listings and reset scheduled import state (cursor + run log).';

    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $listingQuery = Listing::query();
        if (! $all) {
            $listingQuery->where(function ($q): void {
                $q->whereNotNull('korter_object_id')
                    ->orWhereNotNull('korter_layout_id');
            });
        }

        $listingCount = (int) $listingQuery->count();
        $runCount = (int) KorterImportRun::query()->count();

        $summary = $all
            ? "Delete all {$listingCount} listing row(s), clear {$runCount} import run log row(s), and reset the import cursor to preset 1 / page 1."
            : "Delete {$listingCount} listing row(s) linked to Korter (korter_object_id or korter_layout_id), clear {$runCount} import run log row(s), and reset the import cursor to preset 1 / page 1.";

        if (! $this->option('force') && ! $this->confirm($summary.' Continue?')) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($all): void {
            if ($all) {
                Listing::query()->delete();
            } else {
                Listing::query()->where(function ($q): void {
                    $q->whereNotNull('korter_object_id')
                        ->orWhereNotNull('korter_layout_id');
                })->delete();
            }

            KorterImportRun::query()->delete();

            $state = KorterImportState::cursorRow();
            $state->next_preset = 1;
            $state->next_page = 1;
            $state->is_idle = false;
            $state->save();
        });

        $this->info('Korter listings removed and import state reset.');

        return self::SUCCESS;
    }
}
