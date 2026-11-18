<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

use MarkupCarve\TempestHighlight\Injections\DelimitedFenceInjection2;
use MarkupCarve\TempestHighlight\Patterns\Carve2CodeBodyPattern;
use MarkupCarve\TempestHighlight\Patterns\Carve2HeadingPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveAttributeBlockPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveBoldItalicPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveBoldPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCaptionMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCodeBodyPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCodeFencePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCommentFencePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveContinuationMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveHeadingPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveHighlightPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveItalicPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveOrderedListMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveStrikePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveTableCellPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveTableHeaderCellPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveThematicBreakPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveUnderlinePattern;
use Tempest\Highlight\Tokens\TokenTypeEnum as T;

class Carve2Language extends CarveLanguage
{
    public function getName(): string
    {
        return 'carve2';
    }

    /**
     * @return array<int, string>
     */
    public function getAliases(): array
    {
        return ['crv2'];
    }

    /**
     * @return array<int, \Tempest\Highlight\Injection>
     */
    public function getInjections(): array
    {
        return [new DelimitedFenceInjection2('csv'), new DelimitedFenceInjection2('tsv')];
    }

    /**
     * @return array<int, \Tempest\Highlight\Pattern>
     */
    public function getPatterns(): array
    {
        $patterns = [];
        foreach (parent::getPatterns() as $pattern) {
            if ($pattern instanceof CarveCommentFencePattern) {
                $patterns[] = new Carve2CommentBodyPattern();
            }
            $replacement = match (true) {
                $pattern instanceof CarveCodeBodyPattern => new Carve2CodeBodyPattern(),
                $pattern instanceof CarveCodeFencePattern => new Carve2Pattern('/^(?<match>[ \\t]*`{3,}[^\\n]*)$/m', T::COMMENT),
                $pattern instanceof CarveHeadingPattern => new Carve2HeadingPattern(),
                $pattern instanceof CarveThematicBreakPattern => new Carve2Pattern('/^(?<match>\\*{3,})[ \\t]*$/m', T::COMMENT),
                $pattern instanceof CarveOrderedListMarkerPattern => new Carve2Pattern('/^[^\\S\\n]*(?<match>\\d+[.)](?= )|(?:[A-Za-z]|[ivxlcdm]{2,}|[IVXLCDM]{2,})(?:\\)(?= )|\\.(?= )))/m', T::PROPERTY),
                $pattern instanceof CarveTableHeaderCellPattern => new Carve2Pattern('(?<match>\\|=(?:\\{(?:[<>~][\\^~v]?|\\?[\\^~v])(?: [^}\\n]*)?\\}(?= ))?)', T::PROPERTY),
                $pattern instanceof CarveTableCellPattern => new Carve2Pattern('(?<match>\\|(?:\\{(?:[<>~][\\^~v]?|\\?[\\^~v])(?: [^}\\n]*)?\\}(?= ))?)', T::PROPERTY),
                $pattern instanceof CarveUnderlinePattern => new Carve2Pattern('(?<match>\\{_[^}\\n]+_\\})', T::GENERIC),
                $pattern instanceof CarveStrikePattern => new Carve2Pattern('(?<match>\\{~[^}\\n]+~\\})', T::GENERIC),
                $pattern instanceof CarveHighlightPattern => new Carve2Pattern('(?<match>\\{=[^}\\n]+=\\})', T::GENERIC),
                $pattern instanceof CarveBoldItalicPattern => new Carve2Pattern('(?<match>\\*/(?!\\s)(?:(?!/\\*).)+(?<!\\s)/\\*)', T::GENERIC),
                $pattern instanceof CarveBoldPattern => new Carve2Pattern('(?<![\\w\\\\*/])(?<match>\\*(?![\\s/])(?:(?!\\*).)+(?<!\\s)\\*)(?![\\w*/])', T::GENERIC),
                $pattern instanceof CarveItalicPattern => new Carve2Pattern('(?<![\\w\\\\/:<*])(?<match>/(?![\\s*])(?:(?![/<>]).)+(?<!\\s)/)(?![\\w*])', T::GENERIC),
                $pattern instanceof CarveCaptionMarkerPattern => new Carve2Pattern('/(?<!\\n\\n)^(?<match>\\^)(?= )/m', T::PROPERTY),
                $pattern instanceof CarveAttributeBlockPattern => $pattern,
                $pattern instanceof CarveContinuationMarkerPattern => null,
                default => $pattern,
            };
            if ($replacement !== null) {
                $patterns[] = $replacement;
            }
        }

        return $patterns;
    }
}
