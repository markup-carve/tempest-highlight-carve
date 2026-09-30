<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Cross-reference. Its link text is cloned from the target.
 */
#[PatternTest(input: 'see </#intro> now', output: '</#intro>')]
final readonly class CarveCrossReferencePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match><\\/#[^>\\s]+>)';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::TYPE;
    }
}
