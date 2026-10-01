<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * ATX heading. Carve puts heading attributes on the line above, never after.
 */
#[PatternTest(input: '# Title', output: '# Title')]
#[PatternTest(input: '###### Six', output: '###### Six')]
#[PatternTest(input: '#no-space', output: null)]
final readonly class CarveHeadingPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>#{1,6}[ \\t]+.*)$/m';
    }

    /**
     * @return array{match: list<array{string, int}>}
     */
    public function match(string $content): array
    {
        preg_match_all('/^(?:> )*#{1,6} +(?![ \t]*$)[^\n]+/m', $content, $headings, PREG_OFFSET_CAPTURE);
        $matches = [];
        foreach ($headings[0] as [$heading, $offset]) {
            preg_match_all('/\\\\.(*SKIP)(*F)|(?:\$\$?|!)?(`+)(?!`)(?:(?!\1(?!`))[^\n])*(?:\1(?!`)|$)|(?<!\S)%%[^\n]*/m', $heading, $inline, PREG_OFFSET_CAPTURE);
            preg_match('/^(?:> )*/', $heading, $quote);
            $cursor = strlen($quote[0]);
            foreach ($inline[0] as [$value, $start]) {
                if ($start > $cursor) {
                    $matches[] = [substr($heading, $cursor, $start - $cursor), $offset + $cursor];
                }
                $cursor = $start + strlen($value);
            }
            if ($cursor < strlen($heading)) {
                $matches[] = [substr($heading, $cursor), $offset + $cursor];
            }
        }

        return ['match' => $matches];
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::KEYWORD;
    }
}
