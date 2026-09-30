<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Bracketed span carrying attributes.
 */
#[PatternTest(input: '[span]{.class}', output: '[span]{.class}')]
final readonly class CarveSpanPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\[[^\\]\\n]+\\]\\{[^}\\n]*\\})';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
