<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Continuation marker: a lone + at column 0, right after a block opener or
 * checkbox, or right after a quote prefix (`>+`). A + with a blank line, the
 * end of input or a sibling list item below it is refused and stays text.
 */
#[PatternTest(input: '- step
+
> note', output: '+')]
#[PatternTest(input: '- +
  > note', output: '+')]
#[PatternTest(input: '> +
> note', output: '+')]
#[PatternTest(input: '>+
> note', output: '+')]
#[PatternTest(input: '>>+
>> note', output: '+')]
#[PatternTest(input: '- - +
    note', output: '+')]
#[PatternTest(input: '1. +
   note', output: '+')]
#[PatternTest(input: '- [ ] +
  note', output: '+')]
#[PatternTest(input: '[^a]: +
  note', output: '+')]
#[PatternTest(input: '- step
  +
  note', output: null)]
#[PatternTest(input: '- step
+

para', output: null)]
#[PatternTest(input: '- step
+', output: null)]
#[PatternTest(input: '- step
+
- next', output: null)]
#[PatternTest(input: 'a + b', output: null)]
final readonly class CarveContinuationMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?:[ \\t]*(?:>[ \\t]*|(?:[-*]|\\d+[.)]|[A-Za-z]+[.)]|:|\\[\\^[^\\]\\r\\n]+\\]:)[ \\t]+(?:\\[[ xX\\-_>?]\\][ \\t]+)?))*'
            . '(?<match>\\+)[ \\t]*(?=\\r?\\n[ \\t]*\\S)(?!\\r?\\n(?:[-*]|\\d+[.)]|[A-Za-z][.)])[ \\t])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
