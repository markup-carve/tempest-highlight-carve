<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Autolink.
 */
#[PatternTest(input: '<https://example.com>', output: '<https://example.com>')]
final readonly class CarveAutolinkPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match><[a-zA-Z][\\w+.-]*:[^>\\s]+>)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
