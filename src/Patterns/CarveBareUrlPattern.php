<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * A bare URL is consumed whole, so its slashes and underscores cannot read as
 * emphasis.
 *
 * The leading guard keeps it OFF a URL that already belongs to a link
 * destination or an autolink. Without it two patterns match the same span and
 * the renderer emits the text twice, which the round-trip test in
 * SampleDocumentTest catches.
 */
#[PatternTest(input: 'Visit https://example.com/a_b_c now', output: 'https://example.com/a_b_c')]
#[PatternTest(input: '[the docs](https://example.com/a)', output: null)]
#[PatternTest(input: '<https://example.com>', output: null)]
final readonly class CarveBareUrlPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<![\\w(<\\[])(?<match>[a-zA-Z][\\w+.-]*:\\/\\/[^\\s<>()\\[\\]]+)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::LITERAL;
    }
}
