<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Auto-numbered list marker, the native form whose width never grows.
 */
#[PatternTest(input: '. auto numbered', output: '.')]
final readonly class CarveAutoNumberMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^[^\\S\\n]*(?<match>\\.)(?=[ \\t])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
