<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Injections;

use MarkupCarve\TempestHighlight\CsvLanguage;
use MarkupCarve\TempestHighlight\TsvLanguage;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Injection;
use Tempest\Highlight\IsInjection;

final readonly class DelimitedFenceInjection implements Injection
{
    use IsInjection;

    public function __construct(private string $language)
    {
    }

    public function getPattern(): string
    {
        return '/^[ \t]*(`{3,}|~{3,})' . $this->language . '[ \t]*\n(?<match>.*?)(?=\n^[ \t]*\1[ \t]*$|\z)/ms';
    }

    public function parseContent(string $content, Highlighter $highlighter): string
    {
        return $highlighter->parse($content, $this->language === 'csv' ? new CsvLanguage() : new TsvLanguage());
    }
}
