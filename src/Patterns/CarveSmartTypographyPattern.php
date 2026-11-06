<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Smart typography shortcuts.
 *
 * A hyphen run after whitespace (or an opening bracket or quote that follows
 * whitespace) and before any non-whitespace character stays text, a CLI flag
 * shape (CARVE-P3-021): `x ---, y` is refused too.
 * An arrow needs its exact hyphen run, so `--->` is a dash and `>`. `->`,
 * `<-`, `<->`, `(c)` and `(r)` are text (carve#4815, carve#4808).
 */
#[PatternTest(input: 'a -- b', output: '--')]
#[PatternTest(input: 'and ... more', output: '...')]
#[PatternTest(input: 'x -> y', output: null)]
#[PatternTest(input: 'a<-b', output: null)]
#[PatternTest(input: '(a), (b) and (c)', output: null)]
#[PatternTest(input: 'x (r) y', output: null)]
#[PatternTest(input: 'x (tm) y', output: '(tm)')]
#[PatternTest(input: 'a --> b', output: '-->')]
#[PatternTest(input: 'a <-- b', output: '<--')]
#[PatternTest(input: 'a <--> b', output: '<-->')]
#[PatternTest(input: 'a ==> b', output: '==>')]
#[PatternTest(input: 'a <== b', output: '<==')]
#[PatternTest(input: 'a <=> b', output: '<=>')]
#[PatternTest(input: 'a != b', output: '!=')]
#[PatternTest(input: 'a <= b', output: '<=')]
#[PatternTest(input: 'a >= b', output: '>=')]
#[PatternTest(input: 'a +- b', output: '+-')]
#[PatternTest(input: 'a{--}b', output: '{--}')]
#[PatternTest(input: 'a--->b', output: '---')]
#[PatternTest(input: 'pages 1--10', output: '--')]
#[PatternTest(input: 'git log --oneline', output: null)]
#[PatternTest(input: '(--x) y', output: null)]
#[PatternTest(input: 'x ---, y', output: null)]
#[PatternTest(input: 'a ---> b', output: null)]
final readonly class CarveSmartTypographyPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/(?:(?<!\\S)|(?<=(?<!\\S)[("\']))(?!-->(?!-))-{2,}(?=[^\\s-])(*SKIP)(*F)'
            . '|(?<match>\\{--\\}|<-->|<--(?!-)|(?<!-)-->|<==|==>|<=>|-{2,}|\\.\\.\\.|!=|<=|>=|\\(tm\\)|\\+-)/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::OPERATOR;
    }
}
