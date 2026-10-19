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

    #[DataProvider('provideDelimitedFenceCases')]
    public function testDelimitedFences(string $source, string $expected): void
    {
        $this->assertSame($expected, $this->highlighter()->parse($source, 'carve'));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideDelimitedFenceCases(): iterable
    {
        yield 'sample csv' => [
            "```csv\nPlan,Seats,Price per month\nStarter,1,\"$0\"\nTeam,10,\"$49, billed yearly\"\n```",
            '<span class="hl-comment">```csv</span>' . "\n"
            . '<span class="hl-variable">Plan</span><span class="hl-operator">,</span><span class="hl-variable">Seats</span><span class="hl-operator">,</span><span class="hl-variable">Price per month</span>' . "\n"
            . '<span class="hl-variable">Starter</span><span class="hl-operator">,</span><span class="hl-variable">1</span><span class="hl-operator">,</span><span class="hl-value">&quot;$0&quot;</span>' . "\n"
            . '<span class="hl-variable">Team</span><span class="hl-operator">,</span><span class="hl-variable">10</span><span class="hl-operator">,</span><span class="hl-value">&quot;$49, billed yearly&quot;</span>' . "\n"
            . '<span class="hl-comment">```</span>',
        ];

        yield 'csv quoted newline and escaped quote' => [
            "```csv\na,\"line one\nline \"\"two\"\n```",
            '<span class="hl-comment">```csv</span>' . "\n"
            . '<span class="hl-variable">a</span><span class="hl-operator">,</span><span class="hl-value">&quot;line one' . "\n"
            . 'line &quot;&quot;two&quot;</span>' . "\n"
            . '<span class="hl-comment">```</span>',
        ];

        yield 'tsv separator and quoted tab' => [
            "```tsv\na,b\tc\n\"x\ty\"\tz\n```",
            '<span class="hl-comment">```tsv</span>' . "\n"
            . '<span class="hl-variable">a,b</span><span class="hl-operator">' . "\t" . '</span><span class="hl-variable">c</span>' . "\n"
            . '<span class="hl-value">&quot;x' . "\t" . 'y&quot;</span><span class="hl-operator">' . "\t" . '</span><span class="hl-variable">z</span>' . "\n"
            . '<span class="hl-comment">```</span>',
        ];

        yield 'text after closing quote stays plain' => [
            "```csv\n\"ok\"tail,next\n```",
            '<span class="hl-comment">```csv</span>' . "\n"
            . '<span class="hl-value">&quot;ok&quot;tail</span><span class="hl-operator">,</span><span class="hl-variable">next</span>' . "\n"
            . '<span class="hl-comment">```</span>',
        ];

        yield 'mid-field quote remains plain' => [
            "```csv\na,un\"quoted,tail\n```",
            '<span class="hl-comment">```csv</span>' . "\n"
            . '<span class="hl-variable">a</span><span class="hl-operator">,</span><span class="hl-variable">un&quot;quoted</span><span class="hl-operator">,</span><span class="hl-variable">tail</span>' . "\n"
            . '<span class="hl-comment">```</span>',
        ];

        yield 'unfinished quote reaches end of body' => [
            "```csv\na,\"unfinished\nnext,line\n```",
            '<span class="hl-comment">```csv</span>' . "\n"
            . '<span class="hl-variable">a</span><span class="hl-operator">,</span><span class="hl-value">&quot;unfinished' . "\n"
            . 'next,line</span>' . "\n" . '<span class="hl-comment">```</span>',
        ];
    }

    #[DataProvider('provideDelimitedFenceCases')]
    public function testDelimitedBodiesLeaveNoPlainText(string $source, string $expected): void
    {
        $html = $this->highlighter()->parse($source, 'carve');
        $body = preg_replace('/<span class="hl-comment">```[a-z]*<\/span>\n|\n<span class="hl-comment">```<\/span>$/', '', $html);
        $stripped = preg_replace('/<span class="hl-[a-z]+">.*?<\/span>/s', '', (string)$body);

        $this->assertSame('', str_replace("\n", '', (string)$stripped));
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

        // An include directive needs whitespace immediately inside both braces.
        // Measured against carve-php 0.1.11: `{{ child.crv }}`,
        // `{{ child.crv#pick }}` and the tab-padded spelling expand, while each
        // of these stays ordinary text.
        yield 'unpadded include' => ['see {{child.crv}} here'];
        yield 'unpadded include with selector' => ['see {{child.crv#pick}} here'];
        yield 'include braces with one space' => ['see {{ }} here'];
        yield 'include braces with only whitespace' => ['see {{  }} here'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIncludeSpellingCases(): iterable
    {
        yield 'spaced' => ['{{ child.crv }}'];
        yield 'spaced with selector' => ['{{ child.crv#pick }}'];
        yield 'tab padded' => ["{{\tchild.crv\t}}"];
        yield 'selector adjacent to a quoted path' => ['{{ "a b.crv"#pick }}'];
    }

    #[DataProvider('provideIncludeSpellingCases')]
    public function testEveryIncludeSpellingTheEngineExpandsIsHighlighted(string $source): void
    {
        $this->assertStringContainsString(
            'hl-type',
            $this->highlighter()->parse($source, 'carve'),
            'carve-php expands this spelling, so it has to read as a directive',
        );
    }

    public function testCommentBeforeParagraphCodeStaysAComment(): void
    {
        $html = $this->highlighter()->parse('a %% hidden `code`', 'carve');
        $this->assertStringContainsString('<span class="hl-comment">%% hidden `code`</span>', $html);
    }

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
