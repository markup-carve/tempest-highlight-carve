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
 * token and ends where a row drops one of the opener's `>` markers. An
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
        $injected = false;
        $comment = null;
        foreach (explode("\n", $content) as $line) {
            $length = strlen($line);
            if ($comment !== null || ($fence === null && preg_match(self::COMMENT_FENCE, $line, $c))) {
                if ($comment === null) {
                    $comment = $c[1];
                } elseif (preg_match(self::COMMENT_FENCE, $line, $c) && strlen($c[1]) >= strlen($comment)) {
                    $comment = null;
                }
                $offset += $length + 1;

                continue;
            }
            if ($fence === null) {
                $this->open($line, $fence, $depth, $injected);
                $offset += $length + 1;

                continue;
            }

            $rest = $this->stripQuotes($line, $depth);
            if ($rest === null) {
                $fence = null;
                $this->open($line, $fence, $depth, $injected);
                $offset += $length + 1;

                continue;
            }

            if (preg_match('/^[ \t]*' . preg_quote($fence[0], '/') . '{' . strlen($fence) . ',}[ \t]*\r?$/', $rest)) {
                $fence = null;
                $offset += $length + 1;

                continue;
            }

            $body = rtrim($rest, "\r");
            if (!$injected && trim($body) !== '') {
                $matches[] = [$body, $offset + $length - strlen($rest)];
            }
            $offset += $length + 1;
        }

        return $matches === [] ? [] : ['match' => $matches];
    }

    public function getTokenType(): TokenType
    {
        return CarveCodeTokenType::Code;
    }

    private function open(string $line, ?string &$fence, int &$depth, bool &$injected): void
    {
        if (!preg_match(self::OPENER, $line, $m) || ($m['fence'][0] === '`' && str_contains($m['info'], '`'))) {
            return;
        }
        $fence = $m['fence'];
        $depth = (int)preg_match_all('/(?<![^ \t])>/', $m['prefix']);
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
