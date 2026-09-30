<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight;

use MarkupCarve\TempestHighlight\Patterns\CarveAttributeBlockPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveAutolinkPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveAutoNumberMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveBareUrlPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveBlockquoteMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveBoldItalicPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveBoldPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCaptionMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCodeFencePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveColonFencePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCommentFencePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCommentPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveContinuationMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCriticMarkupPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveCrossReferencePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveDefinitionBodyPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveDefinitionTermPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveDelimitedCommentPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveEscapePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveFootnoteReferencePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveFrontmatterDelimiterPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveHeadingPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveHighlightPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveImagePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveIncludePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveInlineCodePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveItalicPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveLinkPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveMathPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveMentionPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveOrderedListMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveRawInlinePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveReferenceDefinitionPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveSmartTypographyPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveSpanPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveStrikePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveSubscriptPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveSuperscriptPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveTableCellPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveTableHeaderCellPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveTaskMarkerPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveThematicBreakPattern;
use MarkupCarve\TempestHighlight\Patterns\CarveUnderlinePattern;
use MarkupCarve\TempestHighlight\Patterns\CarveUnorderedListMarkerPattern;
use Tempest\Highlight\Languages\Base\BaseLanguage;

/**
 * Carve support for tempest/highlight.
 *
 * ORDER IS THE CONTRACT. Patterns apply in the order this method returns them,
 * and Carve marks emphasis with single characters - / * _ ~ = - that occur
 * constantly inside URLs, code spans and identifiers. Every construct that may
 * CONTAIN one is therefore listed before the emphasis patterns. Moving an
 * emphasis pattern up turns a URL into an italic, silently.
 */
class CarveLanguage extends BaseLanguage
{
    public function getName(): string
    {
        return 'carve';
    }

    /**
     * @return array<int, string>
     */
    public function getAliases(): array
    {
        return ['crv'];
    }

    /**
     * @return array<int, \Tempest\Highlight\Pattern>
     */
    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new CarveFrontmatterDelimiterPattern(),
            new CarveCommentFencePattern(),
            new CarveCommentPattern(),
            new CarveDelimitedCommentPattern(),
            new CarveCodeFencePattern(),
            new CarveColonFencePattern(),
            new CarveHeadingPattern(),
            new CarveThematicBreakPattern(),
            new CarveAttributeBlockPattern(),
            new CarveBlockquoteMarkerPattern(),
            new CarveContinuationMarkerPattern(),
            new CarveCaptionMarkerPattern(),
            new CarveDefinitionTermPattern(),
            new CarveDefinitionBodyPattern(),
            new CarveTaskMarkerPattern(),
            new CarveUnorderedListMarkerPattern(),
            new CarveOrderedListMarkerPattern(),
            new CarveAutoNumberMarkerPattern(),
            new CarveTableHeaderCellPattern(),
            new CarveTableCellPattern(),
            new CarveReferenceDefinitionPattern(),
            new CarveIncludePattern(),
            new CarveEscapePattern(),
            new CarveRawInlinePattern(),
            new CarveMathPattern(),
            new CarveInlineCodePattern(),
            new CarveCrossReferencePattern(),
            new CarveAutolinkPattern(),
            new CarveBareUrlPattern(),
            new CarveImagePattern(),
            new CarveSpanPattern(),
            new CarveLinkPattern(),
            new CarveFootnoteReferencePattern(),
            new CarveSuperscriptPattern(),
            new CarveSubscriptPattern(),
            new CarveCriticMarkupPattern(),
            new CarveBoldItalicPattern(),
            new CarveBoldPattern(),
            new CarveItalicPattern(),
            new CarveUnderlinePattern(),
            new CarveStrikePattern(),
            new CarveHighlightPattern(),
            new CarveMentionPattern(),
            new CarveSmartTypographyPattern(),
        ];
    }
}
