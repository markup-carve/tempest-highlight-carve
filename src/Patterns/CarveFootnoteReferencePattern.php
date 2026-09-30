<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Footnote reference, and the opener of an inline note.
 */
#[PatternTest(input: 'text[^1] more', output: '[^1]')]
#[PatternTest(input: 'an ^[inline note]', output: '^[')]
final readonly class CarveFootnoteReferencePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\[\\^[^\\]\\n]+\\]|\\^\\[)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
