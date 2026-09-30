<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Task-list state marker, including the four states beyond checked and unchecked.
 */
#[PatternTest(input: '- [x] done', output: '[x]')]
#[PatternTest(input: '- [ ] todo', output: '[ ]')]
#[PatternTest(input: '- [?] maybe', output: '[?]')]
final readonly class CarveTaskMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^[ \\t]*(?:[-*+]|\\d+[.)]|\\.)[ \\t]+(?<match>\\[[ xX\\-_>?]\\])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::KEYWORD;
    }
}
