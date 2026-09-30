<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Superscript. Braced only in Carve, so a bare ^x^ stays literal.
 */
#[PatternTest(input: 'mc{^2^}', output: '{^2^}')]
final readonly class CarveSuperscriptPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\{\\^[^}\\n]+\\^\\})';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::GENERIC;
    }
}
