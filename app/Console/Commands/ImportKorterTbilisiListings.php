<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\KorterTbilisiListingsImportService;
use Illuminate\Console\Command;

class ImportKorterTbilisiListings extends Command
{
    protected $signature = 'korter:import-tbilisi
                            {--page=1 : Which Korter listing index page to fetch (1-based)}
                            {--preset=1: Aparatments in Tblisi}
                            {--skip-detail-images : Only use cover image from the list (no per-listing detail requests)}
                            {--delay-ms=150 : Pause between detail page requests (0 to disable)}';

    protected $description = 'Fetch apartment listings from korter.ge (Tbilisi, EN) and upsert into the local listings table.';

    public function handle(KorterTbilisiListingsImportService $import): int
    {
        $preset = (int) $this->option('preset');
        if (! KorterTbilisiListingsImportService::presetExists($preset)) {
            $this->error('Preset "'.$preset.'" not found');

            return self::FAILURE;
        }

        $page = max(1, (int) $this->option('page'));
        $skipDetail = (bool) $this->option('skip-detail-images');
        $delayMs = max(0, (int) $this->option('delay-ms'));

        $result = $import->importListingPage($preset, $page, $skipDetail, $delayMs, $this->output);

        if (! $result->httpOk) {
            return self::FAILURE;
        }

        if (! $result->parseOk) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
