<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Attribute block: id, classes and key=value pairs.
 */
#[PatternTest(input: '{#intro .featured}', output: '{#intro .featured}')]
#[PatternTest(input: '{.c}', output: '{.c}')]
final readonly class CarveAttributeBlockPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\{[#.][^}\\n]*\\})';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::ATTRIBUTE;
    }
}
