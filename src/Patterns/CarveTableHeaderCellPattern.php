<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Table header cell marker with its optional alignment axes.
 */
#[PatternTest(input: '|= Header |', output: '|=')]
#[PatternTest(input: '|=> Age |', output: '|=>')]
final readonly class CarveTableHeaderCellPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\|=[<>~^v?]*)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
