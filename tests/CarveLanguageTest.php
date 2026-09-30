<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Test;

use MarkupCarve\TempestHighlight\CarveLanguage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Highlight\Highlighter;

final class CarveLanguageTest extends TestCase
{
    private function highlighter(): Highlighter
    {
        return (new Highlighter())->addLanguage(new CarveLanguage());
    }

    public function testRegistersItsNameAndAlias(): void
    {
        $highlighter = $this->highlighter();

        $this->assertSame(
            $highlighter->parse('# Title', 'carve'),
            $highlighter->parse('# Title', 'crv'),
        );
    }

    #[DataProvider('provideHighlightCases')]
    public function testHighlight(string $source, string $expected): void
    {
        $this->assertSame($expected, $this->highlighter()->parse($source, 'carve'));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideHighlightCases(): iterable
    {
        yield 'heading' => [
            '# Release notes',
            '<span class="hl-keyword"># Release notes</span>',
        ];

        yield 'emphasis' => [
            'This has /italic/ and *bold* text',
            'This has <span class="hl-generic">/italic/</span> and <span class="hl-generic">*bold*</span> text',
        ];

        yield 'line comment' => [
            '%% a note',
            '<span class="hl-comment">%% a note</span>',
        ];

        yield 'cross reference' => [
            'See </#intro> now',
            'See <span class="hl-type">&lt;/#intro&gt;</span> now',
        ];
    }

    /**
     * Carve reads a doubled delimiter as literal text, so highlighting the
     * inner run would tell the reader the opposite of what the document does.
     * This is the single most likely regression in this package: a Markdown
     * habit reaching into a Carve grammar.
     *
     * @param string $source
     */
    #[DataProvider('provideLiteralCases')]
    public function testDoubledDelimitersStayPlain(string $source): void
    {
        $this->assertSame(
            $source,
            $this->highlighter()->parse($source, 'carve'),
            'a doubled delimiter is literal text in Carve and must not be highlighted',
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideLiteralCases(): iterable
    {
        yield 'double asterisk' => ['In Carve **this** is literal'];
        yield 'double tilde' => ['In Carve ~~this~~ is literal'];
        yield 'bare superscript' => ['A bare ^2^ is literal'];
        yield 'bare subscript' => ['A bare ,2, is literal'];
        yield 'snake case' => ['a snake_case_name here'];
        yield 'key value' => ['a key=value pair'];
    }

    /**
     * A URL is dense in the exact characters Carve marks emphasis with. The
     * ordering in CarveLanguage::getPatterns() is what keeps it intact.
     */
    public function testABareUrlSurvivesIntact(): void
    {
        $this->assertSame(
            'Visit <span class="hl-literal">https://example.com/a_b_c</span> now',
            $this->highlighter()->parse('Visit https://example.com/a_b_c now', 'carve'),
        );
    }

    public function testSupAndSubAreBracedOnly(): void
    {
        $this->assertSame(
            'mc<span class="hl-generic">{^2^}</span>',
            $this->highlighter()->parse('mc{^2^}', 'carve'),
        );
    }
}
