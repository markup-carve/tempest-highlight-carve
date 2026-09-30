<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Image.
 */
#[PatternTest(input: '![alt](img.jpg)', output: '![alt](img.jpg)')]
final readonly class CarveImagePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>!\\[[^\\]\\n]*\\]\\([^)\\n]*\\))';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
