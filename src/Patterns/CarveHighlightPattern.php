<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Highlight.
 */
#[PatternTest(input: 'a =h= b', output: '=h=')]
#[PatternTest(input: 'key=value', output: null)]
final readonly class CarveHighlightPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<![\\w\\\\=])(?<match>=(?!\\s)(?:(?!=).)+(?<!\\s)=)(?!\\w|=)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::GENERIC;
    }
}
