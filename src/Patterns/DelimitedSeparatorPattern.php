<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

#[PatternTest(input: 'a,b', output: ',')]
final readonly class DelimitedSeparatorPattern implements Pattern
{
    use IsPattern;

    public function __construct(private string $separator = ',')
    {
    }

    public function getPattern(): string
    {
        return '/(?<match>' . preg_quote($this->separator, '/') . ')/';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::OPERATOR;
    }
}
