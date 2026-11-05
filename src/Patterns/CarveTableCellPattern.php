<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Table cell separator, with a data cell's glued alignment run.
 */
#[PatternTest(input: '| Cell |', output: '|')]
#[PatternTest(input: '|> 30 |', output: '|>')]
#[PatternTest(input: '|~v x |', output: '|~v')]
#[PatternTest(input: '|?^ x |', output: '|?^')]
#[PatternTest(input: '|>x |', output: '|')]
final readonly class CarveTableCellPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\|(?:[<>~][\\^~v]?|\\?[\\^~v])(?= )|\\|)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
