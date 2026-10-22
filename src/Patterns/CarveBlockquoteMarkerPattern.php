<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Blockquote marker. A `>` needs a space or the line end after it, so `>>`
 * and `>x` are paragraph text.
 */
#[PatternTest(input: '> > nested', output: '>')]
#[PatternTest(input: '>> text', output: null)]
final readonly class CarveBlockquoteMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>[^\\S\\n]*>)(?=[ \\t]|$)/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
