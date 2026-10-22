<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

/**
 * Rows inside a code fence, its closing line included, for the patterns that
 * read the document line by line.
 */
final class FencedLines
{
    /**
     * @param array<int, string> $lines
     *
     * @return array<int, true>
     */
    public static function of(array $lines): array
    {
        $fenced = [];
        $fence = null;
        foreach ($lines as $row => $line) {
            $bare = ltrim($line, " \t>");
            if ($fence !== null) {
                $fenced[$row] = true;
                if (preg_match('/^' . preg_quote($fence[0], '/') . '{' . strlen($fence) . ',}[ \t]*$/', $bare)) {
                    $fence = null;
                }

                continue;
            }
            if (preg_match('/^(`{3,}|~{3,})/', $bare, $m)) {
                $fence = $m[1];
            }
        }

        return $fenced;
    }
}
