<?php

declare(strict_types=1);

namespace App\Services;

final readonly class KorterPageImportResult
{
    public function __construct(
        public bool $httpOk,
        public bool $parseOk,
        public bool $hadApartments,
        public int $importedCount,
        public string $url,
    ) {}
}
