<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use MarkupCarve\TempestHighlight\FencedLines;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Definition-list body marker. A `: ` line is a body only inside a
 * definition list, under its term or an earlier body; after a quote, a list
 * item or a paragraph it is lazy paragraph text (markup-carve/carve#3493).
 */
#[PatternTest(input: ':: term
:  definition', output: ':')]
#[PatternTest(input: ':: term
: a
lazy
: b', output: ':')]
#[PatternTest(input: '> a
: b', output: null)]
#[PatternTest(input: 'para
: b', output: null)]
#[PatternTest(input: '```
: b
```', output: null)]
final readonly class CarveDefinitionBodyPattern implements Pattern
{
    /**
     * @return array{match?: array<int, array{0: string, 1: int}>}
     */
    public function match(string $content): array
    {
        $lines = explode("\n", $content);
        $fenced = FencedLines::of($lines);
        $body = [];
        $found = [];
        $offset = 0;
        foreach ($lines as $row => $line) {
            $body[$row] = !isset($fenced[$row]) && preg_match('/^:[ \t]/', $line) && $this->underTerm($lines, $fenced, $body, $row);
            if ($body[$row]) {
                $found[] = [':', $offset];
            }
            $offset += strlen($line) + 1;
        }

        return $found === [] ? [] : ['match' => $found];
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }

    /**
     * @param array<int, string> $lines
     * @param array<int, true> $fenced
     * @param array<int, bool> $body
     * @param int $row
     */
    private function underTerm(array $lines, array $fenced, array $body, int $row): bool
    {
        $blank = false;
        for ($j = $row - 1; $j >= 0; $j--) {
            $line = rtrim($lines[$j], "\r");
            if (isset($fenced[$j])) {
                return false;
            }
            if (trim($line) === '') {
                $blank = true;

                continue;
            }
            if (str_starts_with($line, ':: ')) {
                return true;
            }
            if (preg_match('/^:[ \t]/', $line)) {
                return $body[$j];
            }
            if ($line[0] === ' ' || $line[0] === "\t") {
                continue;
            }
            // A paragraph line after a blank starts a new block; without one it is lazy body text.
            if ($blank || preg_match('/^(?:>|[-*][ \t]|(?:\d+|[A-Za-z])[.)][ \t]|#|`{3}|~{3}|\|)/', $line)) {
                return false;
            }
        }

        return false;
    }
}
