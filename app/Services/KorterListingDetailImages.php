<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Extracts gallery image URLs from Korter detail HTML (`window.INITIAL_STATE`).
 *
 * Resale listings use {@see layoutLandingStore.layout.images}.
 * Primary / new-build layout pages use {@see layoutLandingStore.building.images.images}
 * and optionally {@see layoutLandingStore.constructionState} galleries.
 */
final class KorterListingDetailImages
{
    public function __construct(
        private readonly KorterInitialStateParser $parser,
    ) {}

    /**
     * @return list<array{file: string, alt: string}>
     */
    public function extractFromHtml(string $html, string $baseAlt): array
    {
        $state = $this->parser->parse($html);
        if ($state === null) {
            return [];
        }

        return $this->extractFromState($state, $baseAlt);
    }

    /**
     * @param  array<string, mixed>  $state
     * @return list<array{file: string, alt: string}>
     */
    public function extractFromState(array $state, string $baseAlt): array
    {
        $sources = [
            data_get($state, 'layoutLandingStore.layout.images'),
            data_get($state, 'layoutLandingStore.building.images.images'),
            $this->flattenConstructionStateImages(data_get($state, 'layoutLandingStore.constructionState')),
        ];

        $out = [];
        foreach ($sources as $raw) {
            if (! is_array($raw) || $raw === []) {
                continue;
            }
            $out = $this->recordsToGallery($raw, $baseAlt);
            if ($out !== []) {
                break;
            }
        }

        if ($out === []) {
            /** @var mixed $layout */
            $layout = data_get($state, 'layoutLandingStore.layout');
            if (is_array($layout)) {
                $url = data_get($layout, 'mediaSrc.default.x2')
                    ?? data_get($layout, 'mediaSrc.default.x1')
                    ?? '';
                if (is_string($url) && $url !== '') {
                    $out[] = [
                        'file' => self::normalizeUrl($url),
                        'alt' => $baseAlt,
                    ];
                }
            }
        }

        return $this->dedupeByFile($out);
    }

    /**
     * @param  list<array<string, mixed>>  $records
     * @return list<array{file: string, alt: string}>
     */
    private function recordsToGallery(array $records, string $baseAlt): array
    {
        $out = [];
        $n = 0;
        foreach ($records as $item) {
            if (! is_array($item)) {
                continue;
            }
            $url = $this->bestUrlFromImageRecord($item);
            if ($url === '') {
                continue;
            }
            $n++;
            $out[] = [
                'file' => self::normalizeUrl($url),
                'alt' => $baseAlt.' ('.$n.')',
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function bestUrlFromImageRecord(array $item): string
    {
        $candidates = [
            data_get($item, 'mediaSrc.default.x2'),
            data_get($item, 'mediaSrc.default.x1'),
            data_get($item, 'mediaSrc.mobile.x2'),
            data_get($item, 'mediaSrc.mobile.x1'),
            data_get($item, 'galleryMediaSrc.default.x2'),
            data_get($item, 'galleryMediaSrc.default.x1'),
            data_get($item, 'galleryMediaSrc.mobile.x2'),
            data_get($item, 'galleryMediaSrc.mobile.x1'),
            $item['src'] ?? null,
        ];

        foreach ($candidates as $url) {
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return '';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function flattenConstructionStateImages(mixed $constructionState): array
    {
        if (! is_array($constructionState) || $constructionState === []) {
            return [];
        }

        $flat = [];
        foreach ($constructionState as $block) {
            if (! is_array($block)) {
                continue;
            }
            /** @var mixed $imgs */
            $imgs = $block['images'] ?? null;
            if (! is_array($imgs)) {
                continue;
            }
            foreach ($imgs as $img) {
                if (is_array($img)) {
                    $flat[] = $img;
                }
            }
        }

        return $flat;
    }

    /**
     * @param  list<array{file: string, alt: string}>  $images
     * @return list<array{file: string, alt: string}>
     */
    private function dedupeByFile(array $images): array
    {
        $seen = [];
        $result = [];
        foreach ($images as $img) {
            if (isset($seen[$img['file']])) {
                continue;
            }
            $seen[$img['file']] = true;
            $result[] = $img;
        }

        return $result;
    }

    public static function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return 'https://korter.ge/'.ltrim($url, '/');
    }
}
