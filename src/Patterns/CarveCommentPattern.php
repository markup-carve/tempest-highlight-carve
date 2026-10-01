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
        return '/\\\\.(*SKIP)(*F)|(?:\$\$?|!)?(`+)(?!`)(?:(?!(?<!`)\1(?!`))[^\n])*(?:(?<!`)\1(?!`)|$)(*SKIP)(*F)|(?<match>(?<!\S)%%[^\n]*)/m';
    }

    /**
     * @return array{match: list<array{string, int}>}
     */
    public function match(string $content): array
    {
        $matches = [];
        $offset = 0;
        foreach (explode("\n", $content) as $line) {
            $pattern = $this->getPattern();
            if (!preg_match('/^(?:> )*(?:#{1,6} |\^ )/', $line)) {
                $pattern = str_replace('|$)', ')', $pattern);
            }
            preg_match_all($pattern, $line, $found, PREG_OFFSET_CAPTURE);
            foreach ($found['match'] as [$value, $start]) {
                $matches[] = [$value, $offset + $start];
            }
            $offset += strlen($line) + 1;
        }

        return ['match' => $matches];
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::COMMENT;
    }
}
