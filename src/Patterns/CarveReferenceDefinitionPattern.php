<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Link, footnote and abbreviation definitions.
 */
#[PatternTest(input: '[ref]: /url', output: '[ref]:')]
#[PatternTest(input: '[^1]: a note', output: '[^1]:')]
#[PatternTest(input: '*[HTML]: HyperText', output: '*[HTML]:')]
final readonly class CarveReferenceDefinitionPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>[ \\t]*(?:\\[\\^?[^\\]\\n]+\\]|\\*\\[[^\\]\\n]+\\]):)(?=[ \\t])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
