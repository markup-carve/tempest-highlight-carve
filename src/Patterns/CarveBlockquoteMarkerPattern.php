<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use MarkupCarve\TempestHighlight\FencedLines;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Blockquote marker. A `>` needs a space or the line end after it, so `>>`
 * and `>x` are paragraph text. Every quote marker in a line's leading run of
 * openers is one, so `> > q` and `- > q` scope each `>`. A line inside a
 * code fence keeps only its first marker, as before.
 */
#[PatternTest(input: '> > nested', output: '>')]
#[PatternTest(input: '>> text', output: null)]
#[PatternTest(input: '- > quoted', output: '>')]
final readonly class CarveBlockquoteMarkerPattern implements Pattern
{
    private const string OPENER = '/\G(?:(?<item>(?:[-*](?:\{[^}\n]*\})?[ \t]+(?:\[[ xX_>?-]\][ \t]+)?|(?:\d+|[A-Za-z])[.)](?:\{[^}\n]*\})?[ \t]+))'
        . '|(?<quote>>(?=[ \t]|$)[ \t]?))/';

    public function getPattern(): string
    {
        return '/^(?<match>[^\S\n]*>)(?=[ \t]|$)/m';
    }

    /**
     * @return array{match?: array<int, array{0: string, 1: int}>}
     */
    public function match(string $content): array
    {
        $found = [];
        $offset = 0;
        $lines = explode("\n", $content);
        $fenced = FencedLines::of($lines);
        foreach ($lines as $row => $line) {
            $pos = strspn($line, " \t");
            $first = true;
            // A fence line keeps only its first marker, which may be the prefix of a quoted fence.
            while (($first || !isset($fenced[$row])) && preg_match(self::OPENER, $line, $m, PREG_UNMATCHED_AS_NULL, $pos)) {
                if (isset($m['quote'])) {
                    // The first marker keeps its indentation in the token, as before.
                    $found[] = $first ? [substr($line, 0, $pos + 1), $offset] : ['>', $offset + $pos];
                }
                $first = false;
                $pos += strlen((string)$m[0]);
                $pos += strspn($line, " \t", $pos);
            }
            $offset += strlen($line) + 1;
        }

        return $found === [] ? [] : ['match' => $found];
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
