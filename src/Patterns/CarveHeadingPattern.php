<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * ATX heading. Carve puts heading attributes on the line above, never after.
 */
#[PatternTest(input: '# Title', output: '# Title')]
#[PatternTest(input: '###### Six', output: '###### Six')]
#[PatternTest(input: '#no-space', output: null)]
final readonly class CarveHeadingPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>#{1,6}[ \\t]+.*)$/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::KEYWORD;
    }
}
