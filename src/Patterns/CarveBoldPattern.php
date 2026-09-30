<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Bold. A doubled delimiter is literal text in Carve, so **this** must not match.
 */
#[PatternTest(input: 'a *bold* b', output: '*bold*')]
#[PatternTest(input: 'In Carve **this** is literal', output: null)]
final readonly class CarveBoldPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<![\\w\\\\*])(?<match>\\*(?!\\s)(?:(?!\\*).)+(?<!\\s)\\*)(?!\\w|\\*)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::GENERIC;
    }
}
