<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Fenced block comment delimiter.
 */
#[PatternTest(input: '%%%
hidden
%%%', output: '%%%')]
final readonly class CarveCommentFencePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>[ \\t]*%{3,})[ \\t]*$/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::COMMENT;
    }
}
