<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Line and trailing comment.
 */
#[PatternTest(input: '%% a note', output: '%% a note')]
#[PatternTest(input: 'text %% trailing', output: '%% trailing')]
final readonly class CarveCommentPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?!(?:> )*(?:#{1,6} |\^ ))[^`\n]*(`+)(?!`)(?:(?!(?<!`)\1(?!`))[\s\S])*?(?<!`)\1(?!`)(*SKIP)(*F)|\\\\.(*SKIP)(*F)|(?:\$\$?|!)?(`+)(?!`)(?:(?!(?<!`)\2(?!`))[^\n])*(?:(?<!`)\2(?!`)|$)(*SKIP)(*F)|(?<match>(?<!\S)%%[^\n]*)/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::COMMENT;
    }
}
