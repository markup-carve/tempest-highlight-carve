<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Definition-list body marker.
 */
#[PatternTest(input: ':  definition', output: ':')]
final readonly class CarveDefinitionBodyPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>:)(?=[ \\t])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
