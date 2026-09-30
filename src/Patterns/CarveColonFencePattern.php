<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Colon fence: divs, admonitions, and the quote / line / hard-break sigils.
 */
#[PatternTest(input: ':::note', output: ':::note')]
#[PatternTest(input: '::: >', output: '::: >')]
#[PatternTest(input: ':::: outer', output: ':::: outer')]
final readonly class CarveColonFencePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>[ \\t]*:{3,}[^\\n]*)$/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::COMMENT;
    }
}
