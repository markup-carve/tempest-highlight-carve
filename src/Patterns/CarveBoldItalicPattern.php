<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Bold italic, matched before the single delimiters.
 */
#[PatternTest(input: 'a /*both*/ b', output: '/*both*/')]
final readonly class CarveBoldItalicPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\/\\*(?!\\s)(?:(?!\\*\\/).)+(?<!\\s)\\*\\/)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::GENERIC;
    }
}
