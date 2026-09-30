<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Raw inline, emitted only when the output format matches.
 */
#[PatternTest(input: '`<br>`{=html}', output: '`<br>`{=html}')]
final readonly class CarveRawInlinePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>`[^`\\n]+`\\{=[a-z]+\\})';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::VALUE;
    }
}
