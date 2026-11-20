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
#[PatternTest(input: ':wiki[unfinished and [other]', output: null)]
#[PatternTest(input: 'https://example.com/a[b]', output: null)]
final readonly class CarveInlineDirectivePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/(?<![A-Za-z0-9_:\\x2f])(?<match>:[A-Za-z][A-Za-z0-9_-]*\\[)(?=(?<body>(?:[^\\[\\]\\r\\n]|\\[(?&body)\\])*)\\])/';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::KEYWORD;
    }
}
