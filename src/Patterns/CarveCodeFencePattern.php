<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

/**
 * Code fence line, including its info string.
 */
#[PatternTest(input: '```php', output: '```php')]
#[PatternTest(input: '```', output: '```')]
#[PatternTest(input: '```=html', output: '```=html')]
final readonly class CarveCodeFencePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(?<match>[ \\t]*(?:`{3,}|~{3,})[^\\n]*)$/m';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::COMMENT;
    }
}
