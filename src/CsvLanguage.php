<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

final class CsvLanguage extends DelimitedTableLanguage
{
    public function getName(): string
    {
        return 'csv';
    }

    protected function separator(): string
    {
        return ',';
    }
}
