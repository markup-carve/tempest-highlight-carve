<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Mention and tag.
 *
 * The leading guard is load-bearing: a cross-reference </#id> and an include
 * {{ file.crv#id }} both carry a # that is NOT a tag, and colouring it as one
 * is the defect this construct has produced in several other Carve grammars.
 */
#[PatternTest(input: 'hi @user', output: '@user')]
#[PatternTest(input: 'see #tag', output: '#tag')]
#[PatternTest(input: 'ends the sentence #tag.', output: '#tag')]
#[PatternTest(input: 'a </#intro> ref', output: null)]
#[PatternTest(input: 'an {{ ch.crv#intro }} include', output: null)]
final readonly class CarveMentionPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<![\\w\\/:<])(?<match>[@#][a-zA-Z][\\w-]*(?:\\.[\\w-]+)*)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::VARIABLE;
    }
}
