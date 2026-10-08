<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Processor include directive. Core never expands one, and the braces stay.
 *
 * The padding immediately inside both braces is part of the directive: carve-php
 * 0.1.11 expands `{{ child.crv }}`, `{{ child.crv#pick }}` and a tab-padded
 * spelling, and leaves `{{child.crv}}` and `{{child.crv#pick}}` as ordinary
 * text, as it does a pair holding only whitespace. So neither is a directive and
 * neither may be painted as one.
 */
#[PatternTest(input: '{{ chapter.crv#intro }}', output: '{{ chapter.crv#intro }}')]
final readonly class CarveIncludePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\{\\{\\s[^}\\s\\n][^}\\n]*\\s\\}\\})';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
