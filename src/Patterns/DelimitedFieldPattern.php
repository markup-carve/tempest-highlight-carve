<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;
use Tempest\Highlight\Tokens\TokenTypeEnum;

#[PatternTest(input: 'a,b', output: 'a')]
final readonly class DelimitedFieldPattern implements Pattern
{
    use IsPattern;

    public function __construct(private string $separator = ',')
    {
    }

    public function getPattern(): string
    {
        $separator = preg_quote($this->separator, '/');

        return '/(?:(?<=\A)|(?<=\n)|(?<=' . $separator . '))(?<match>(?!")[^' . $separator . '\n]+)/';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return TokenTypeEnum::VARIABLE;
    }
}
