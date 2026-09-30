<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Backslash escape of any ASCII punctuation.
 */
#[PatternTest(input: 'a \\*literal\\* b', output: '\\*')]
final readonly class CarveEscapePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\\\[!-\\/:-@\\[-`{-~])';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::LITERAL;
    }
}
