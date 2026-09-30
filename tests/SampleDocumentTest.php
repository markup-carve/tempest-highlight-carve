<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Test;

use MarkupCarve\TempestHighlight\CarveLanguage;
use PHPUnit\Framework\TestCase;
use Tempest\Highlight\Highlighter;

/**
 * Whole-document checks over `tests/Fixtures/sample.crv`.
 *
 * The unit cases pin one construct each in isolation. This pins what the
 * per-pattern cases cannot see: that the patterns still behave once they
 * compete over the same document, which is where an ordering mistake shows up.
 *
 * The fixture is canonical Carve, verified with `carve lint` and
 * `carve fmt --check` against carve-rs ea87db0.
 */
final class SampleDocumentTest extends TestCase
{
    private function parsedSample(): string
    {
        $source = file_get_contents(__DIR__ . '/Fixtures/sample.crv');
        self::assertNotFalse($source, 'the sample fixture is unreadable');

        return (new Highlighter())
            ->addLanguage(new CarveLanguage())
            ->parse($source, 'carve');
    }

    public function testEveryLineOfTheSampleProducesSomeHighlighting(): void
    {
        $this->assertStringContainsString('hl-keyword', $this->parsedSample());
        $this->assertStringContainsString('hl-generic', $this->parsedSample());
        $this->assertStringContainsString('hl-comment', $this->parsedSample());
        $this->assertStringContainsString('hl-property', $this->parsedSample());
        $this->assertStringContainsString('hl-type', $this->parsedSample());
    }

    /**
     * Highlighting must not lose or invent a character. Stripping the markup
     * has to give the source back byte for byte, modulo HTML escaping.
     */
    public function testHighlightingPreservesEveryCharacter(): void
    {
        $source = file_get_contents(__DIR__ . '/Fixtures/sample.crv');
        self::assertNotFalse($source);

        $stripped = html_entity_decode(
            strip_tags($this->parsedSample()),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        $this->assertSame($source, $stripped);
    }

    /**
     * The URL in the fixture carries a `//` and two `_` runs. If the emphasis
     * patterns ever move ahead of the URL pattern, this is what says so.
     */
    public function testTheUrlInTheSampleIsNotBrokenIntoEmphasis(): void
    {
        $this->assertStringContainsString(
            '<span class="hl-literal">https://example.com/a_b_c</span>',
            $this->parsedSample(),
        );
    }
}
