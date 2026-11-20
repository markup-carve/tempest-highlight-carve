<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Test;

use MarkupCarve\TempestHighlight\CarveLanguage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Highlight\Highlighter;

final class CarveLanguageTest extends TestCase
{
    #[DataProvider('inventory')]
    public function testUpgradeInventory(string $source, string $fragment, bool $present): void
    {
        $html = (new Highlighter())->addLanguage(new CarveLanguage())->parse($source, 'carve');
        self::assertSame($present, str_contains($html, $fragment), $html);
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function inventory(): iterable
    {
        yield 'U-001 lazy heading' => ["text\n# Heading\n", 'hl-keyword', false];
        yield 'U-014 removed plus attachment' => ["- first\n+\n> note\n", 'hl-property">+</span>', false];
        yield 'U-031 one-line attribute' => ["{.note}\nParagraph.\n", 'hl-attribute', true];
        yield 'U-032 wrapped attribute' => ["{.a\n .b}\nParagraph.\n", 'hl-attribute', false];
        yield 'U-044 underscore break removed' => ["para\n\n___\n", 'hl-comment', false];
        yield 'U-045 spaced break removed' => ["* * *\n\n- - -\n", 'hl-comment', false];
        yield 'U-049 wrong-column closer' => ["```\ncode\n   ```\nafter\n", 'carve-code">after</span>', true];
        yield 'U-050 false comment closer' => ["%%%\nhidden\n%%% visible?\nafter\n", 'hl-comment">after', true];
        yield 'U-055 GFM separator removed' => ["| a | b |\n|---|--:|\n", 'hl-property">|---', false];
        yield 'U-056 glued alignment removed' => ["|=< a |=> b |=~ c |\n", 'hl-property">|=&lt;', false];
        yield 'U-063 detached caption' => ["| a | b |\n\n^ Caption\n", 'hl-property">^', false];
        yield 'U-065 bare underline removed' => ["an _underlined_ word\n", 'hl-generic', false];
        yield 'U-066 bare strike removed' => ["a ~struck~ word\n", 'hl-generic', false];
        yield 'U-067 bare mark removed' => ["a =marked= word\n", 'hl-generic', false];
        yield 'U-068 old combined order removed' => ["/*bold italic*/\n", 'hl-generic">/*bold italic*/', false];
        yield 'U-069 substitution removed' => ["{~old~>new~}\n", 'hl-operator', false];
        yield 'U-101 equals heading' => ["= Heading\n", 'hl-keyword', true];
        yield 'U-103 letter marker one space' => ["a. one\n", 'hl-property">a.', true];
        yield 'U-104 quoted colon title' => ["::: > \"T\"\nquoted\n:::\n", 'hl-comment', true];
        yield 'U-105 quoted fence title' => ["```=html \"T\"\n<b>x</b>\n```\n", 'hl-comment', true];
        yield 'U-106 definition term continuation' => [":: term\nmore term text\n: first description\n", 'hl-property', true];
        yield 'U-107 caption continuation' => ["| a | b |\n^ first\n^ second\n", 'hl-property">^', true];
        yield 'U-289 include shift' => ["{{ a.crv @shift:1 }}\n", 'hl-', true];
        yield 'CC-112 region marker' => ["a\n%% #region x\nb", 'hl-comment', true];
        yield 'CC-893 glued header alignment removed' => ["|=< a |\n", 'hl-property">|=&lt;', false];
        yield 'CC-894 braced header alignment' => ["|={<} a |\n", 'hl-property">|={&lt;}', true];
    }

    public function testPublicRegistration(): void
    {
        $language = new CarveLanguage();
        self::assertSame('carve', $language->getName());
        self::assertContains('crv', $language->getAliases());
    }

    public function testInlineDirectivesAndFenceBodies(): void
    {
        $highlighter = (new Highlighter())->addLanguage(new CarveLanguage());
        foreach (['wiki', 'term', 'cite', 'index', 'color', 'spoiler'] as $name) {
            self::assertStringContainsString('hl-keyword">:' . $name . '[</span>', $highlighter->parse(':' . $name . '[value]', 'carve'));
        }
        self::assertStringNotContainsString('hl-keyword">:wiki[</span>', $highlighter->parse("```\n:wiki[body]\n```", 'carve'));
        self::assertStringNotContainsString('hl-keyword">:wiki[</span>', $highlighter->parse(':wiki[unfinished and [other]', 'carve'));
        self::assertStringContainsString('hl-keyword">:wiki[</span>', $highlighter->parse(':wiki[' . str_repeat('[x]', 3000) . ']', 'carve'));
        self::assertStringContainsString('hl-keyword">:wiki[</span>', $highlighter->parse(':wiki[`[`]', 'carve'));
    }

    public function testRetainedInlineAndFenceShapes(): void
    {
        $highlighter = (new Highlighter())->addLanguage(new CarveLanguage());
        foreach (['{^sup^}', '{,sub,}', '{_under_}', '{~strike~}', '{=mark=}', '*/both/*'] as $source) {
            self::assertStringContainsString('hl-generic', $highlighter->parse($source, 'carve'));
        }
        self::assertStringNotContainsString('hl-comment', $highlighter->parse("~~~\ntext\n~~~", 'carve'));
    }

    public function testReviewCasesFollowTheModel(): void
    {
        $highlighter = (new Highlighter())->addLanguage(new CarveLanguage());
        self::assertStringContainsString('hl-keyword', $highlighter->parse("text\n\n# Heading\n", 'carve'));
        self::assertSame(2, substr_count($highlighter->parse("# One\n# Two\n", 'carve'), 'hl-keyword'));
        self::assertStringContainsString('hl-keyword', $highlighter->parse("```\ncode\n```\n# After\n", 'carve'));
        $strike = $highlighter->parse('{~old~>new~}', 'carve');
        self::assertStringContainsString('hl-generic', $strike);
        self::assertStringNotContainsString('hl-attribute', $strike);
        self::assertStringContainsString('hl-property', $highlighter->parse("a.  one\n", 'carve'));
        $code = $highlighter->parse("```\n%%%\n```\n# Heading\nplain\n", 'carve');
        self::assertStringContainsString('hl-keyword', $code);
        self::assertStringNotContainsString('hl-comment">plain', $code);
    }
}
