<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * CriticMarkup editorial spans.
 */
#[PatternTest(input: '{+added+}', output: '{+added+}')]
#[PatternTest(input: '{-gone-}', output: '{-gone-}')]
#[PatternTest(input: '{~old~>new~}', output: '{~old~>new~}')]
final readonly class CarveCriticMarkupPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\{\\+[^}\\n]*\\+\\}|\\{-[^}\\n]*-\\}|\\{~[^}\\n]*~\\}|\\{\\#[^}\\n]*\\#\\})';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::ATTRIBUTE;
    }
}
