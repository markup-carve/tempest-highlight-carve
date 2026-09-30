<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Italic.
 *
 * Two guards, both load-bearing. The lookbehind keeps a URL scheme and path
 * out. Excluding < and > from the content stops a run opening at a
 * cross-reference </#id> and closing at some later slash on the same line.
 */
#[PatternTest(input: 'a /it/ b', output: '/it/')]
#[PatternTest(input: 'https://example.com/a', output: null)]
#[PatternTest(input: 'see </#intro> and /x/', output: '/x/')]
final readonly class CarveItalicPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<![\\w\\\\\\/:<])(?<match>\\/(?!\\s)(?:(?![\\/<>]).)+(?<!\\s)\\/)(?!\\w)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::GENERIC;
    }
}
