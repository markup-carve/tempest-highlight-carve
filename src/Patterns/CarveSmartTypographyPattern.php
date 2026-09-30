<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Smart typography shortcuts.
 */
#[PatternTest(input: 'a -- b', output: '--')]
#[PatternTest(input: 'and ... more', output: '...')]
#[PatternTest(input: 'x -> y', output: '->')]
final readonly class CarveSmartTypographyPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>---|--|\\.\\.\\.|->|\\(c\\)|\\(r\\)|\\(tm\\))';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::OPERATOR;
    }
}
