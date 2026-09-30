<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Ordered list marker, including the letter dialects.
 */
#[PatternTest(input: '1. first', output: '1.')]
#[PatternTest(input: 'a) alpha', output: 'a)')]
final readonly class CarveOrderedListMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^[^\\S\\n]*(?<match>(?:\\d+|[A-Za-z])[.)])(?=[ \\t])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
