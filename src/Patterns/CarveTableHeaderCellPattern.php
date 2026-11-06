<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Table header cell marker with its optional alignment axes, braced
 * (`|={>}`, carve#5091) or in the deprecated glued 0.1.13 spelling (`|=>`).
 */
#[PatternTest(input: '|= Header |', output: '|=')]
#[PatternTest(input: '|=> Age |', output: '|=>')]
#[PatternTest(input: '|={>} Age |', output: '|={>}')]
#[PatternTest(input: '|={<^ .x} a |', output: '|={<^ .x}')]
#[PatternTest(input: '|={?v} a |', output: '|={?v}')]
#[PatternTest(input: '|={.x} a |', output: '|=')]
final readonly class CarveTableHeaderCellPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\\|=(?:\\{(?:[<>~][\\^~v]?|\\?[\\^~v])(?: [^}\\n]*)?\\}(?= )|[<>~^v?]*))';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::PROPERTY;
    }
}
