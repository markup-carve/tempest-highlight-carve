<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenTypeEnum;

final readonly class Carve2Pattern implements Pattern
{
    use IsPattern;

    public function __construct(private string $pattern, private TokenTypeEnum $tokenType)
    {
    }

    public function getPattern(): string
    {
        return str_starts_with($this->pattern, '/')
            ? $this->pattern
            : '/' . str_replace('/', '\\/', $this->pattern) . '/';
    }

    public function getTokenType(): TokenTypeEnum
    {
        return $this->tokenType;
    }
}
