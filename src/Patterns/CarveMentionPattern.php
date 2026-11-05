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
 * An include's options (`@rows`, `@lines`) are not mentions either. A name
 * holds at least one ASCII letter, anywhere (carve#4809): `#v2` and `#2v` are
 * tags, `#123` and `@5` are text.
 */
#[PatternTest(input: 'hi @user', output: '@user')]
#[PatternTest(input: 'see #tag', output: '#tag')]
#[PatternTest(input: 'see #2v', output: '#2v')]
#[PatternTest(input: 'Issue #123', output: null)]
#[PatternTest(input: 'hi @5', output: null)]
#[PatternTest(input: 'ends the sentence #tag.', output: '#tag')]
#[PatternTest(input: 'a </#intro> ref', output: null)]
#[PatternTest(input: 'an {{ ch.crv#intro }} include', output: null)]
#[PatternTest(input: '{{ data.csv @rows:2-5 @cols:1 }}', output: null)]
#[PatternTest(input: '{{ a.crv @lines:1-2 }} @user', output: '@user')]
final readonly class CarveMentionPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/\\{\\{\\s[^}\\n]*\\}\\}(*SKIP)(*F)|(?<![\\w\\/:<])(?<match>[@#](?=[\\w.-]*?[a-zA-Z])[\\w-]+(?:\\.[\\w-]+)*)/';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::VARIABLE;
    }
}
