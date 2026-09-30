<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Underline. Never fires inside a word.
 */
#[PatternTest(input: 'a _u_ b', output: '_u_')]
#[PatternTest(input: 'snake_case_name', output: null)]
final readonly class CarveUnderlinePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<![\\w\\\\_])(?<match>_(?!\\s)(?:(?!_).)+(?<!\\s)_)(?!\\w)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::GENERIC;
    }
}
