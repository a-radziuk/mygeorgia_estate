<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Extracts JSON from `window.INITIAL_STATE = {...}` embedded in Korter HTML.
 * Uses brace depth with string/escape awareness so values containing "{" do not break parsing.
 */
final class KorterInitialStateParser
{
    /**
     * @return array<string, mixed>|null
     */
    public function parse(string $html): ?array
    {
        if (! preg_match('/window\.INITIAL_STATE\s*=\s*(\{)/s', $html, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = $m[1][1];
        $json = $this->extractBalancedObject($html, $start);
        if ($json === null) {
            return null;
        }

        try {
            /** @var array<string, mixed> $data */
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            return $data;
        } catch (\JsonException) {
            return null;
        }
    }

    private function extractBalancedObject(string $html, int $start): ?string
    {
        $len = strlen($html);
        $depth = 0;
        $inString = false;
        $escape = false;

        for ($i = $start; $i < $len; $i++) {
            $c = $html[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;

                    continue;
                }
                if ($c === '\\') {
                    $escape = true;

                    continue;
                }
                if ($c === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($c === '"') {
                $inString = true;

                continue;
            }

            if ($c === '{') {
                $depth++;
            } elseif ($c === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($html, $start, $i - $start + 1);
                }
            }
        }

        return null;
    }
}
