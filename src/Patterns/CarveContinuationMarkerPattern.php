<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use MarkupCarve\TempestHighlight\FencedLines;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Continuation marker: a lone `+` that the engine reads as a marker.
 *
 * Two forms (markup-carve/carve#3445). The first-block form ends an opener
 * line (`- +`, `1. +`, `- [ ] +`, `: +`, `[^a]: +`, `> +`). The separate-line
 * form stands at the marker column of an open container, with a non-list
 * block at that column on the next line. Every other `+` is text, including
 * one inside a code fence. A regex cannot see which containers are open, so
 * `match()` reads the document line by line.
 */
#[PatternTest(input: '- step
+
> note', output: '+')]
#[PatternTest(input: '- a
  - b
  +
  > q
  +
  para', output: '+')]
#[PatternTest(input: '- +', output: '+')]
#[PatternTest(input: ':: t
: +', output: '+')]
#[PatternTest(input: '> +
flush', output: '+')]
#[PatternTest(input: '> - a
>+
> para', output: '+')]
#[PatternTest(input: 'x[^a]

[^a]: +
para', output: '+')]
#[PatternTest(input: '```
+
```', output: null)]
#[PatternTest(input: '- step
+

para', output: null)]
#[PatternTest(input: '- step
+
- next', output: null)]
#[PatternTest(input: '> q
> +
> para', output: null)]
#[PatternTest(input: '> +
> para', output: null)]
#[PatternTest(input: '1. [ ] +
para', output: null)]
#[PatternTest(input: 'a + b', output: null)]
final readonly class CarveContinuationMarkerPattern implements Pattern
{
    private const string ITEM = 'item';

    private const string QUOTE = 'quote';

    private const string HOLDER = 'holder';

    private const string NOTE = 'note';

    private const string OPENER = '/\G(?:(?<item>(?:[-*](?:\{[^}\n]*\})?[ \t]+(?:\[[ xX_>?-]\][ \t]+)?|(?:\d+|[A-Za-z])[.)](?:\{[^}\n]*\})?[ \t]+))'
        . '|(?<quote>>(?:[ \t]|$))|(?<holder>:[ \t]+)|(?<note>\[\^[^\]\n]+\]:[ \t]+))/';

    private const string BLOCK_LINE = '/^(?:`{3,}|~{3,}|:{3,}|#{1,6}(?:[ \t]|$)|\|)/';

    /**
     * @return array{match?: array<int, array{0: string, 1: int}>}
     */
    public function match(string $content): array
    {
        $lines = explode("\n", $content);
        $starts = [];
        $offset = 0;
        foreach ($lines as $i => $line) {
            $starts[$i] = $offset;
            $offset += strlen($line) + 1;
            $lines[$i] = rtrim($line, "\r");
        }

        $fenced = FencedLines::of($lines);
        $memo = ['cols' => [], 'marker' => [], 'open' => $this->openQuoteDepths($lines, $fenced)];
        $found = [];
        foreach ($lines as $row => $line) {
            if (isset($fenced[$row]) || !preg_match('/^[^+]*\+[ \t]*$/', $line)) {
                continue;
            }
            $plus = (int)strrpos($line, '+');
            if ($this->isMarker($lines, $fenced, $memo, $row, $plus)) {
                $found[] = ['+', $starts[$row] + $plus];
            }
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
     * @param array{cols: array<string, array<int, array{int, string}>>, marker: array<int, bool>, open: array<int, int>} $memo
     * @param int $plus
     * @param int $row
     */
    private function isMarker(array $lines, array $fenced, array &$memo, int $row, int $plus): bool
    {
        return $memo['marker'][$row] ??= $this->readMarker($lines, $fenced, $memo, $row, $plus);
    }

    /**
     * @param array<int, string> $lines
     * @param array<int, true> $fenced
     * @param array{cols: array<string, array<int, array{int, string}>>, marker: array<int, bool>, open: array<int, int>} $memo
     * @param int $plus
     * @param int $row
     */
    private function readMarker(array $lines, array $fenced, array &$memo, int $row, int $plus): bool
    {
        $line = $lines[$row];
        $prefixDepth = $this->quoteDepth($line);
        $openDepth = $memo['open'][$row];

        if ($prefixDepth > $openDepth) {
            return $this->quoteOpenerAttaches($lines, $fenced, $memo, $row, $openDepth, $prefixDepth);
        }

        $shape = $this->shape($line, $prefixDepth);
        if ($shape === null || $shape[1] === null) {
            return false;
        }
        [$indent, $openers, $end] = $shape;
        if ($openers !== []) {
            // The opener run must reach the `+` itself: `1. [ ] +` is item text.
            if ($end !== $plus) {
                return false;
            }
            $kind = $openers[count($openers) - 1][1];

            return match ($kind) {
                self::ITEM, self::HOLDER => true,
                self::NOTE => $this->noteAttaches($lines, $row, $prefixDepth, $openers[count($openers) - 1][0]),
                default => false,
            };
        }

        $glued = $prefixDepth > 0 && $plus > 0 && $line[$plus - 1] === '>';

        return $this->separateLineAttaches($lines, $fenced, $memo, $row, $prefixDepth, $indent, $glued);
    }

    /**
     * The separate-line form: the `+` at an open container's marker column,
     * followed by a non-list block at that column.
     *
     * @param array<int, string> $lines
     * @param array<int, true> $fenced
     * @param array{cols: array<string, array<int, array{int, string}>>, marker: array<int, bool>, open: array<int, int>} $memo
     * @param bool $glued
     * @param int $column
     * @param int $depth
     * @param int $row
     */
    private function separateLineAttaches(
        array $lines,
        array $fenced,
        array &$memo,
        int $row,
        int $depth,
        int $column,
        bool $glued,
    ): bool {
        $open = false;
        $itemAtColumn = false;
        foreach ($this->openColumns($lines, $fenced, $memo, $row, $depth) as [$col, $kind]) {
            // Behind a glued `>+` the engine attaches to a list item, not to a quote.
            if ($col === $column && (!$glued || $kind === self::ITEM)) {
                $open = true;
                $itemAtColumn = $itemAtColumn || $kind === self::ITEM;
            }
        }
        if (!$open || !isset($lines[$row + 1]) || isset($fenced[$row + 1])) {
            return false;
        }
        $follower = $this->shape($lines[$row + 1], $depth);
        if ($follower === null || $follower[1] === null || $follower[0] !== $column) {
            return false;
        }
        $first = $follower[1][0] ?? null;
        // Sibling rule: a list item after a `+` that sits at a list item's column.
        if ($first !== null && $first[0] === $column && $first[1] === self::ITEM && $itemAtColumn) {
            return false;
        }

        // Inside a quote, a spaced `> +` at the quote's own column does not take a nested quote.
        return !($depth > 0 && !$glued && $column === 0 && $first !== null && $first[0] === 0 && $first[1] === self::QUOTE);
    }

    /**
     * `> +` that opens a quote: the next line must hold a flush-left block
     * that neither continues the new quote nor starts a list item.
     *
     * @param array<int, string> $lines
     * @param array<int, true> $fenced
     * @param array{cols: array<string, array<int, array{int, string}>>, marker: array<int, bool>, open: array<int, int>} $memo
     * @param int $prefixDepth
     * @param int $openDepth
     * @param int $row
     */
    private function quoteOpenerAttaches(
        array $lines,
        array $fenced,
        array &$memo,
        int $row,
        int $openDepth,
        int $prefixDepth,
    ): bool {
        $line = $lines[$row];
        if (!preg_match('/>[ \t]?\+[ \t]*$/', $line)) {
            return false;
        }
        $stripped = $this->unquote($line, $openDepth);
        if ($stripped === null) {
            return false;
        }
        $quoteColumn = $this->columnAt($line, $stripped[0] + strspn($line, " \t", $stripped[0]), $stripped) - $stripped[1];
        if ($quoteColumn > 0) {
            $nested = false;
            foreach ($this->openColumns($lines, $fenced, $memo, $row, $openDepth) as [$col, $kind]) {
                if ($kind === self::ITEM && $col < $quoteColumn) {
                    $nested = true;
                }
            }
            if (!$nested) {
                return false;
            }
        }
        if (!isset($lines[$row + 1]) || isset($fenced[$row + 1])) {
            return false;
        }
        $next = $lines[$row + 1];
        $depth = min($this->quoteDepth($next), $prefixDepth - 1);
        $follower = $this->shape($next, $depth);
        if ($follower === null || $follower[1] === null) {
            return false;
        }
        $first = $follower[1][0] ?? null;
        if ($first !== null && $first[0] === $follower[0] && in_array($first[1], [self::ITEM, self::QUOTE], true)) {
            return false;
        }
        $innermost = $this->unquote($line, $prefixDepth - 1);
        $limit = $quoteColumn;
        if ($depth === $prefixDepth - 1 && $innermost !== null) {
            $limit = $this->columnAt($line, $innermost[0] + strspn($line, " \t", $innermost[0]), $innermost) - $innermost[1];
        }

        return $follower[0] <= $limit;
    }

    /**
     * `[^a]: +` takes a flush-left block that is not a list item.
     *
     * @param array<int, string> $lines
     * @param int $column
     * @param int $depth
     * @param int $row
     */
    private function noteAttaches(array $lines, int $row, int $depth, int $column): bool
    {
        if (!isset($lines[$row + 1])) {
            return false;
        }
        $follower = $this->shape($lines[$row + 1], $depth);
        if ($follower === null || $follower[1] === null || $follower[0] !== $column) {
            return false;
        }
        $first = $follower[1][0] ?? null;

        return !($first !== null && $first[1] === self::ITEM);
    }

    /**
     * Per row, the quote depth of the paragraph or block the line follows:
     * the nearest line above that is blank, quoted, an opener or a block line.
     *
     * @param array<int, string> $lines
     * @param array<int, true> $fenced
     *
     * @return array<int, int>
     */
    private function openQuoteDepths(array $lines, array $fenced): array
    {
        $open = [];
        $carry = 0;
        foreach ($lines as $row => $line) {
            $open[$row] = $carry;
            if (isset($fenced[$row])) {
                $carry = 0;

                continue;
            }
            $shape = $this->shape($line, 0);
            if ($shape === null || $shape[1] === null) {
                $carry = 0;

                continue;
            }
            $depth = $this->quoteDepth($line);
            if ($depth > 0 || $shape[1] !== [] || preg_match(self::BLOCK_LINE, ltrim($line, " \t"))) {
                $carry = $depth;
            }
        }

        return $open;
    }

    /**
     * Marker columns of the containers still open at `$row`, read inside
     * `$depth` quotes. A list item stays open across a blank line; a quote,
     * a description or a footnote does not.
     *
     * @param array<int, string> $lines
     * @param array<int, true> $fenced
     * @param array{cols: array<string, array<int, array{int, string}>>, marker: array<int, bool>, open: array<int, int>} $memo
     * @param int $depth
     * @param int $row
     *
     * @return array<int, array{int, string}>
     */
    private function openColumns(array $lines, array $fenced, array &$memo, int $row, int $depth): array
    {
        $key = $row . ':' . $depth;
        if (isset($memo['cols'][$key])) {
            return $memo['cols'][$key];
        }
        $found = [];
        $ceiling = PHP_INT_MAX;
        $blank = false;
        $itemFloor = PHP_INT_MAX;
        for ($j = $row - 1; $j >= 0; $j--) {
            if (isset($fenced[$j])) {
                continue;
            }
            $shape = $this->shape($lines[$j], $depth);
            if ($shape === null) {
                break;
            }
            [$indent, $openers] = $shape;
            if ($openers === null) {
                $blank = true;

                continue;
            }
            $plusColumn = $openers === [] ? $this->lonePlusColumn($lines[$j], $depth) : null;
            if ($plusColumn !== null && $this->isMarker($lines, $fenced, $memo, $j, (int)strrpos($lines[$j], '+'))) {
                // A block the marker attached keeps the container at its column
                // open, unless a sibling item below already closed it.
                $ceiling = $ceiling >= $plusColumn && $itemFloor > $plusColumn
                    ? $plusColumn + 1
                    : min($ceiling, $plusColumn + 1);
            } elseif ($openers !== []) {
                foreach ($openers as [$col, $kind]) {
                    if ($col < $ceiling && ($kind === self::ITEM || !$blank)) {
                        $found[] = [$col, $kind];
                    }
                }
                $ceiling = min($ceiling, $openers[0][0]);
                if ($openers[0][1] === self::ITEM) {
                    $itemFloor = min($itemFloor, $openers[0][0]);
                }
            } elseif (!$this->isLazy($lines, $fenced, $j, $depth)) {
                $ceiling = min($ceiling, $indent);
            }
            if ($ceiling <= 0) {
                break;
            }
            if (isset($memo['cols'][$j . ':' . $depth])) {
                foreach ($memo['cols'][$j . ':' . $depth] as [$col, $kind]) {
                    if ($col < $ceiling && ($kind === self::ITEM || !$blank)) {
                        $found[] = [$col, $kind];
                    }
                }

                break;
            }
        }

        return $memo['cols'][$key] = $found;
    }

    /**
     * @param array<int, string> $lines
     * @param array<int, true> $fenced
     * @param int $depth
     * @param int $row
     */
    private function isLazy(array $lines, array $fenced, int $row, int $depth): bool
    {
        if (preg_match(self::BLOCK_LINE, ltrim($lines[$row], " \t>")) || $row === 0 || isset($fenced[$row - 1])) {
            return false;
        }
        $above = $this->shape($lines[$row - 1], $depth);

        return $above !== null && $above[1] !== null;
    }

    private function lonePlusColumn(string $line, int $depth): ?int
    {
        $stripped = $this->unquote($line, $depth);
        if ($stripped === null || !preg_match('/^[ \t]*\+[ \t]*$/', substr($line, $stripped[0]))) {
            return null;
        }

        return $this->columnAt($line, $stripped[0] + strspn($line, " \t", $stripped[0]), $stripped) - $stripped[1];
    }

    /**
     * Indent and opener run of a line read inside `$depth` quotes, relative to
     * the quote's content column. Null when the line is not inside that many
     * quotes; null openers for a blank line.
     *
     * @return array{0: int, 1: array<int, array{int, string}>|null, 2: int}|null
     */
    private function shape(string $line, int $depth): ?array
    {
        $stripped = $this->unquote($line, $depth);
        if ($stripped === null) {
            return null;
        }
        [$at, $base] = $stripped;
        $lead = $at + strspn($line, " \t", $at);
        $indent = $this->columnAt($line, $lead, $stripped) - $base;
        $length = strlen($line);
        if ($lead >= $length) {
            return [$indent, null, $lead];
        }
        $openers = [];
        $pos = $lead;
        $at = [$lead, $indent + $base];
        while ($pos < $length && preg_match(self::OPENER, $line, $m, PREG_UNMATCHED_AS_NULL, $pos)) {
            $kind = match (true) {
                isset($m['item']) => self::ITEM,
                isset($m['quote']) => self::QUOTE,
                isset($m['holder']) => self::HOLDER,
                default => self::NOTE,
            };
            $at = [$pos, $this->columnAt($line, $pos, $at)];
            $openers[] = [$at[1] - $base, $kind];
            $pos += strlen((string)$m[0]);
            $pos += strspn($line, " \t", $pos);
        }

        return [$indent, $openers, $pos];
    }

    /**
     * Strip `$depth` quote prefixes: the offset and column of what follows.
     *
     * @return array{int, int}|null
     */
    private function unquote(string $line, int $depth, int &$reached = 0): ?array
    {
        $reached = 0;
        $at = 0;
        $col = 0;
        $length = strlen($line);
        // `>q` is a paragraph; only a lone `+` may sit glued to its prefix (`>+`, `>>+`).
        $glued = (bool)preg_match('/^[ \t>]*\+[ \t]*$/', $line);
        for ($i = 0; $i < $depth; $i++) {
            $start = $col;
            while ($at < $length && ($line[$at] === ' ' || $line[$at] === "\t") && $col - $start < 3) {
                $col = $line[$at] === "\t" ? $col + 4 - $col % 4 : $col + 1;
                $at++;
            }
            if ($at >= $length || $line[$at] !== '>') {
                return null;
            }
            $at++;
            $col++;
            if (!$glued && $at < $length && $line[$at] !== ' ' && $line[$at] !== "\t") {
                return null;
            }
            if ($at < $length && ($line[$at] === ' ' || $line[$at] === "\t")) {
                $col = $line[$at] === "\t" ? $col + 4 - $col % 4 : $col + 1;
                $at++;
            }
            $reached++;
        }

        return [$at, $col];
    }

    private function quoteDepth(string $line): int
    {
        $depth = 0;
        $this->unquote($line, PHP_INT_MAX, $depth);

        return $depth;
    }

    /**
     * Visual column of byte `$offset`, counting tab stops of four from `$from`.
     *
     * @param string $line
     * @param int $offset
     * @param array{int, int} $from
     */
    private function columnAt(string $line, int $offset, array $from): int
    {
        [$at, $col] = $from;
        for ($i = $at; $i < $offset; $i++) {
            $col = $line[$i] === "\t" ? $col + 4 - $col % 4 : $col + 1;
        }

        return $col;
    }
}
