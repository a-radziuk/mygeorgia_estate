<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\KorterImportRun;
use App\Models\KorterImportState;
use App\Services\KorterTbilisiListingsImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportKorter extends Command
{
    protected $signature = 'korter:import';

    protected $description = 'Scheduled Korter import: walks presets and pages, logging each run; idles after all presets are exhausted.';

    public function handle(KorterTbilisiListingsImportService $import): int
    {
        $state = KorterImportState::cursorRow();

        if ($state->is_idle || $state->next_preset >= KorterTbilisiListingsImportService::FIRST_INVALID_PRESET) {
            if (! $state->is_idle) {
                $state->is_idle = true;
                $state->save();
            }

            KorterImportRun::query()->create([
                'preset' => null,
                'page' => null,
                'imported_count' => 0,
                'had_apartments' => false,
                'http_ok' => true,
                'parse_ok' => true,
                'idle_skip' => true,
                'url' => null,
                'note' => 'Idle: no more presets to import.',
            ]);

            $this->comment('Korter import idle (all presets finished).');

            return self::SUCCESS;
        }

        $preset = (int) $state->next_preset;
        $page = max(1, (int) $state->next_page);

        if (! KorterTbilisiListingsImportService::presetExists($preset)) {
            $state->is_idle = true;
            $state->save();

            KorterImportRun::query()->create([
                'preset' => $preset,
                'page' => $page,
                'imported_count' => 0,
                'had_apartments' => false,
                'http_ok' => false,
                'parse_ok' => false,
                'idle_skip' => true,
                'url' => null,
                'note' => 'Preset not defined; marked idle.',
            ]);

            $this->warn('Invalid preset in state; marked idle.');

            return self::SUCCESS;
        }

        $result = $import->importListingPage($preset, $page, false, 150, $this->output);

        DB::transaction(function () use ($state, $preset, $page, $result): void {
            KorterImportRun::query()->create([
                'preset' => $preset,
                'page' => $page,
                'imported_count' => $result->importedCount,
                'had_apartments' => $result->hadApartments,
                'http_ok' => $result->httpOk,
                'parse_ok' => $result->parseOk,
                'idle_skip' => false,
                'url' => $result->url,
                'note' => null,
            ]);

            if (! $result->httpOk || ! $result->parseOk) {
                return;
            }

            if ($result->hadApartments) {
                $state->next_page = $page + 1;
            } else {
                $state->next_preset = $preset + 1;
                $state->next_page = 1;
                if ($state->next_preset >= KorterTbilisiListingsImportService::FIRST_INVALID_PRESET) {
                    $state->is_idle = true;
                }
            }

            $state->save();
        });

        if (! $result->httpOk || ! $result->parseOk) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
