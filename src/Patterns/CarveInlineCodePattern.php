<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Inline code span.
 */
#[PatternTest(input: 'use `code` here', output: '`code`')]
final readonly class CarveInlineCodePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/\\\\.(*SKIP)(*F)|(?<match>(`+)(?!`)(?:(?!\2(?!`))[^\n])*(?:\2(?!`)|$))/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::VALUE;
    }
}
