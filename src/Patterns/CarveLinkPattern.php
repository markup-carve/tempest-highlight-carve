<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Inline link, and the wiki-style reference link that resolves to a heading.
 */
#[PatternTest(input: '[text](url)', output: '[text](url)')]
#[PatternTest(input: '[Page Name][]', output: '[Page Name][]')]
final readonly class CarveLinkPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\[[^\\]\\n]+\\]\\([^)\\n]*\\)|\\[[^\\]\\n]+\\]\\[[^\\]\\n]*\\])';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
