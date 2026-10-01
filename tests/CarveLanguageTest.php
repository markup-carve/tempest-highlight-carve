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

        yield 'heading code and comment' => [
            '# a `x %% b` c %% hidden',
            '<span class="hl-keyword"># a </span><span class="hl-value">`x %% b`</span><span class="hl-keyword"> c </span><span class="hl-comment">%% hidden</span>',
        ];

        yield 'caption code and comment' => [
            '^ cap `x %% b` c %% hidden',
            '<span class="hl-property">^</span> cap <span class="hl-value">`x %% b`</span> c <span class="hl-comment">%% hidden</span>',
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
    public function testMultilineParagraphCodeKeepsItsTrailingComment(): void
    {
        $html = $this->highlighter()->parse("a `x\ny` %% c", 'carve');
        $this->assertStringContainsString('<span class="hl-comment">%% c</span>', $html);
    }

    public function testHeadingAndCaptionCommentBoundaries(): void
    {
        foreach (['# a ', '^ cap ', '> # a ', '> ^ cap '] as $prefix) {
            foreach (['`x %% b`', '``x %% b``', '!`x %% b`', '$`x %% b`', '`x %% b', '` x `` y %% hidden', '``x```y %% hidden', '$$`x %% b`'] as $body) {
                $source = $prefix . $body . "\n\nplain tail";
                $html = $this->highlighter()->parse($source, 'carve');
                $this->assertStringNotContainsString('hl-comment', $html, $source);
                $this->assertStringEndsWith("\n\nplain tail", $html);
            }
            foreach ([1, 2, 3, 4] as $slashes) {
                $source = $prefix . str_repeat(chr(92), $slashes) . '`x %% hidden';
                $html = $this->highlighter()->parse($source, 'carve');
                if ($slashes % 2 === 1) {
                    $this->assertStringContainsString('hl-comment', $html, $source);
                } else {
                    $this->assertStringNotContainsString('hl-comment', $html, $source);
                }
            }
        }
    }

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
