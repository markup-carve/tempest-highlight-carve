<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Strikethrough. A doubled delimiter is literal text in Carve.
 */
#[PatternTest(input: 'a ~s~ b', output: '~s~')]
#[PatternTest(input: 'In Carve ~~this~~ is literal', output: null)]
final readonly class CarveStrikePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<![\\w\\\\~])(?<match>~(?!\\s)(?:(?!~).)+(?<!\\s)~)(?!\\w|~)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::GENERIC;
    }
}
