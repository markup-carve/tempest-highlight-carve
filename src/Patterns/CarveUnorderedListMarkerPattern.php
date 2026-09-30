<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Unordered list marker.
 */
#[PatternTest(input: '- item', output: '-')]
#[PatternTest(input: '* item', output: '*')]
final readonly class CarveUnorderedListMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^[^\\S\\n]*(?<match>[-*+])(?=[ \\t])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
