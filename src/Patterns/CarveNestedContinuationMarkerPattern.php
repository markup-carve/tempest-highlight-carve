<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * An indented lone + at the marker column of an open nested list item.
 * Only blank or deeper lines may sit between the item and the +, and the
 * follower rules of CarveContinuationMarkerPattern apply.
 */
#[PatternTest(input: '- a
  - b
  +
  > note', output: '+')]
#[PatternTest(input: '- a
  - b

    more
  +
  > note', output: '+')]
#[PatternTest(input: '- a
  - b
  +
  - c', output: null)]
#[PatternTest(input: '- a
  - b
  +

  c', output: null)]
#[PatternTest(input: '- a
  +
  > note', output: null)]
#[PatternTest(input: '- a
  - b
 +
 > note', output: null)]
final readonly class CarveNestedContinuationMarkerPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<ind>[ \\t]+)(?:[-*]|\\d+[.)]|[A-Za-z][.)])[ \\t][^\\r\\n]*'
            . '(?>\\r?\\n(?:[ \\t]*(?=\\r?\\n|$)|\\k<ind>[ \\t]+\\S[^\\r\\n]*))*?'
            . '\\r?\\n\\k<ind>(?<match>\\+)[ \\t]*(?=\\r?\\n[ \\t]*\\S)(?!\\r?\\n\\k<ind>(?:[-*]|\\d+[.)]|[A-Za-z][.)])[ \\t])/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
