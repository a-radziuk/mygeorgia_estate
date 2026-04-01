<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Extracts gallery image URLs from a Korter apartment detail page HTML
 * (`window.INITIAL_STATE.layoutLandingStore.layout.images`).
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
        /** @var mixed $raw */
        $raw = data_get($state, 'layoutLandingStore.layout.images');
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        $n = 0;
        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }
            $n++;
            $url = data_get($item, 'mediaSrc.default.x2')
                ?? data_get($item, 'mediaSrc.default.x1')
                ?? data_get($item, 'mediaSrc.mobile.x2')
                ?? data_get($item, 'mediaSrc.mobile.x1')
                ?? '';
            if (! is_string($url) || $url === '') {
                continue;
            }
            $out[] = [
                'file' => self::normalizeUrl($url),
                'alt' => $baseAlt.' ('.$n.')',
            ];
        }

        return $this->dedupeByFile($out);
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
