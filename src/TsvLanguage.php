<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

final class TsvLanguage extends DelimitedTableLanguage
{
    public function getName(): string
    {
        return 'tsv';
    }

    protected function separator(): string
    {
        return "\t";
    }
}
