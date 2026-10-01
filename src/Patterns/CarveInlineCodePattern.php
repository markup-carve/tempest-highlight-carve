<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Inline code span.
 */
#[PatternTest(input: 'use `code` here', output: '`code`')]
final readonly class CarveInlineCodePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/\\\\.(*SKIP)(*F)|(?<match>(`+)(?!`)(?:(?!(?<!`)\2(?!`))[^\n])*(?:(?<!`)\2(?!`)|$))/m';
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
        return TokenTypeEnum::VALUE;
    }
}
