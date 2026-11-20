<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Name and opening bracket of a 0.2 inline directive.
 */
#[PatternTest(input: 'See :wiki[Home] now', output: ':wiki[')]
#[PatternTest(input: ':term[Tree] :cite[doe] :index[Tree]', output: ':term[')]
#[PatternTest(input: ':color[red] and :spoiler[secret]', output: ':color[')]
#[PatternTest(input: ':unknown[content]', output: ':unknown[')]
#[PatternTest(input: ':wiki[outer [inner]]', output: ':wiki[')]
#[PatternTest(input: ':wiki[`[`]', output: ':wiki[')]
#[PatternTest(input: ':wiki[unfinished and [other]', output: null)]
#[PatternTest(input: '\\:wiki[escaped]', output: null)]
#[PatternTest(input: 'https://example.com/a[b]', output: null)]
final readonly class CarveInlineDirectivePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/(?<![A-Za-z0-9_:\\x2f\\x5c])(?<match>:[A-Za-z][A-Za-z0-9_-]*\\[)/';
    }

    /**
     * @return array{match: list<array{string, int}>}
     */
    public function match(string $content): array
    {
        preg_match_all($this->getPattern(), $content, $candidates, PREG_OFFSET_CAPTURE);
        $stack = [];
        $closing = [];
        $length = strlen($content);
        for ($i = 0; $i < $length; $i++) {
            $char = $content[$i];
            if ($char === '\\' && $i + 1 < $length && $content[$i + 1] !== "\n") {
                $i++;
            } elseif ($char === '`') {
                $run = strspn($content, '`', $i);
                $needle = str_repeat('`', $run);
                $next = $i + $run;
                $lineEnd = strpos($content, "\n", $next);
                $lineEnd = $lineEnd === false ? $length : $lineEnd;
                while (($close = strpos($content, $needle, $next)) !== false && $close < $lineEnd) {
                    $after = $close + $run;
                    if (
                        ($close === 0 || $content[$close - 1] !== '`')
                        && ($after >= $length || $content[$after] !== '`')
                    ) {
                        $i = $after - 1;

                        continue 2;
                    }
                    $next = $after;
                }
                $i += $run - 1;
            } elseif ($char === "\n") {
                $stack = [];
            } elseif ($char === '[') {
                $stack[] = $i;
            } elseif ($char === ']' && $stack !== []) {
                $closing[array_pop($stack)] = true;
            }
        }
        $matches = [];
        foreach ($candidates['match'] ?? [] as [$value, $offset]) {
            if (isset($closing[$offset + strlen($value) - 1])) {
                $matches[] = [$value, $offset];
            }
        }

        return ['match' => $matches];
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::KEYWORD;
    }
}
