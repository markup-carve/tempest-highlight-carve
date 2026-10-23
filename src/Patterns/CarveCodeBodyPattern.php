<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use MarkupCarve\TempestHighlight\Tokens\CarveCodeTokenType;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenType;

/**
 * The body of a code fence, one token per row.
 *
 * The token holds no children, so headings, emphasis and markers inside the
 * fence stay plain code. A fence in a quote keeps its `>` prefix outside the
 * token and ends where a row drops one of the opener's `>` markers. Only a
 * closer at the opener's column closes it; a row left of that column ends the
 * holding item, and with it the fence. An
 * unprefixed `csv` or `tsv` body is left to its injection, and a backtick line
 * inside a `%%%` comment opens nothing.
 */
#[PatternTest(input: '```
# not a heading
```', output: '# not a heading')]
#[PatternTest(input: '> ```
> *a*
> ```', output: '*a*')]
#[PatternTest(input: '```
```', output: null)]
final readonly class CarveCodeBodyPattern implements Pattern
{
    private const string OPENER = '/^(?<prefix>(?:[ \t]*(?:>|[-*]|\d+[.)]|[A-Za-z][.)])(?=[ \t]))*[ \t]*)(?<fence>`{3,}|~{3,})(?<info>[^\n]*)$/';

    /**
     * Info strings whose body `DelimitedFenceInjection` highlights instead.
     */
    private const string INJECTED = '/^(?:csv|tsv)[ \t]*\r?$/';

    private const string COMMENT_FENCE = '/^[ \t]*(%{3,})[ \t]*\r?$/';

    /**
     * @return array{match?: array<int, array{0: string, 1: int}>}
     */
    public function match(string $content): array
    {
        $matches = [];
        $offset = 0;
        $fence = null;
        $depth = 0;
        $indent = 0;
        $injected = false;
        $comment = null;
        foreach (explode("\n", $content) as $line) {
            $length = strlen($line);
            $start = $offset;
            $offset += $length + 1;
            if ($fence !== null) {
                $rest = $this->stripQuotes($line, $depth);
                $blank = $rest !== null && trim($rest) === '';
                $column = $rest === null ? 0 : strspn($rest, " \t");
                if ($rest !== null && ($blank || $column >= $indent)) {
                    if ($column === $indent && preg_match('/^' . preg_quote($fence[0], '/') . '{' . strlen($fence) . ',}[ \t]*\r?$/', substr($rest, $column))) {
                        $fence = null;

                        continue;
                    }
                    $body = rtrim($rest, "\r");
                    if (!$injected && !$blank) {
                        $matches[] = [$body, $start + $length - strlen($rest)];
                    }

                    continue;
                }
                // The quote or item that holds the fence ended on this line.
                $fence = null;
            }
            if ($comment !== null) {
                if (preg_match(self::COMMENT_FENCE, $line, $c) && strlen($c[1]) >= strlen($comment)) {
                    $comment = null;
                }

                continue;
            }
            if (preg_match(self::COMMENT_FENCE, $line, $c)) {
                $comment = $c[1];

                continue;
            }
            $this->open($line, $fence, $depth, $indent, $injected);
        }

        return $matches === [] ? [] : ['match' => $matches];
    }

    public function getTokenType(): TokenType
    {
        return CarveCodeTokenType::Code;
    }

    private function open(string $line, ?string &$fence, int &$depth, int &$indent, bool &$injected): void
    {
        if (!preg_match(self::OPENER, $line, $m) || ($m['fence'][0] === '`' && str_contains($m['info'], '`'))) {
            return;
        }
        $fence = $m['fence'];
        $depth = (int)preg_match_all('/(?<![^ \t])>/', $m['prefix']);
        $indent = strlen((string)$this->stripQuotes($m['prefix'], $depth));
        $injected = trim($m['prefix']) === '' && preg_match(self::INJECTED, $m['info']) === 1;
    }

    private function stripQuotes(string $line, int $depth): ?string
    {
        $rest = $line;
        for ($i = 0; $i < $depth; $i++) {
            if (!preg_match('/^[ \t]*>[ ]?/', $rest, $m)) {
                return null;
            }
            $rest = substr($rest, strlen($m[0]));
        }

        return $rest;
    }
}
