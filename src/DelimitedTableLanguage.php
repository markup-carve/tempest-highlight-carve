<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

use MarkupCarve\TempestHighlight\Patterns\DelimitedFieldPattern;
use MarkupCarve\TempestHighlight\Patterns\DelimitedSeparatorPattern;
use MarkupCarve\TempestHighlight\Patterns\DelimitedStringPattern;
use Tempest\Highlight\Languages\Base\BaseLanguage;

abstract class DelimitedTableLanguage extends BaseLanguage
{
    abstract protected function separator(): string;

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new DelimitedStringPattern($this->separator()),
            new DelimitedFieldPattern($this->separator()),
            new DelimitedSeparatorPattern($this->separator()),
        ];
    }
}
