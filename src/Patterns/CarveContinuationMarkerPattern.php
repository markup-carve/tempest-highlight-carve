<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Continuation marker: a lone + at column 0, or right after a block opener.
 */
#[PatternTest(input: '- step
+
> note', output: '+')]
#[PatternTest(input: '- +', output: '+')]
#[PatternTest(input: '> +', output: '+')]
#[PatternTest(input: '- - +', output: '+')]
#[PatternTest(input: '1. +', output: '+')]
#[PatternTest(input: '[^a]: +', output: '+')]
#[PatternTest(input: '- step
  +', output: null)]
#[PatternTest(input: 'a + b', output: null)]
final readonly class CarveContinuationMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?:[ \\t]*(?:>|[-*]|\\d+[.)]|[A-Za-z]+[.)]|:|\\[\\^[^\\]\\r\\n]+\\]:)[ \\t]+)*(?<match>\\+)[ \\t]*$/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
