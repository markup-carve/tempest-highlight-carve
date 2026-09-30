# Carve for tempest/highlight

[![CI](https://img.shields.io/github/actions/workflow/status/markup-carve/tempest-highlight-carve/ci.yml?branch=main&style=flat-square)](https://github.com/markup-carve/tempest-highlight-carve/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/markup-carve/tempest-highlight-carve?style=flat-square)](https://packagist.org/packages/markup-carve/tempest-highlight-carve)
[![Total Downloads](https://img.shields.io/packagist/dt/markup-carve/tempest-highlight-carve?style=flat-square)](https://packagist.org/packages/markup-carve/tempest-highlight-carve)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat-square)](https://phpstan.org/)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-8892BF.svg?style=flat-square)](https://php.net)
[![Software License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)

Server-side syntax highlighting for the [Carve](https://github.com/markup-carve/carve)
markup language, as a language for [tempest/highlight](https://github.com/tempestphp/highlight).

```bash
composer require markup-carve/tempest-highlight-carve
```

## Usage

Register the language on a highlighter and parse with `carve` or `crv`:

```php
use MarkupCarve\TempestHighlight\CarveLanguage;
use Tempest\Highlight\Highlighter;

$highlighter = (new Highlighter())->addLanguage(new CarveLanguage());

echo $highlighter->parse($source, 'carve');
```

Styling comes from tempest/highlight's own themes, so an existing stylesheet
needs no change. To highlight `.crv` blocks inside Markdown rendered through
the CommonMark extension, register the language on the highlighter you hand to
that extension; nothing else differs.

## What it highlights

Headings, thematic breaks, lists (unordered, ordered, the letter dialects, the
auto-numbering `.` form and every task state), definition lists, blockquotes and
their continuation markers, tables with their alignment axes, captions, code and
colon fences, comments in all three spellings, attribute blocks, links, images,
spans, autolinks, cross-references, footnotes, includes, math, CriticMarkup,
mentions and tags, smart typography, and the full emphasis set.

## Carve is not Markdown, and the grammar knows it

Most of the work here is in what does **not** get highlighted. Carve's emphasis
uses single delimiters, so the Markdown doubles are ordinary text:

| Written | Carve reads it as |
|---------|-------------------|
| `/italic/` `*bold*` `_underline_` `~strike~` `=highlight=` | emphasis |
| `**bold**` `~~strike~~` | literal text |
| `{^sup^}` `{,sub,}` | superscript, subscript |
| `^sup^` `,sub,` | literal text |

A highlighter that does not know this does not leave the construct alone - it
reads it as whatever its own syntax spells, and tells the reader the opposite of
what the document does. The same trap catches `snake_case_name`, `key=value`,
and any bare URL, which is dense in exactly the characters Carve marks emphasis
with. Each of those is pinned by a test.

Pattern **order** is the mechanism. tempest/highlight applies patterns in the
order `CarveLanguage::getPatterns()` returns them, so every construct that may
contain a delimiter is listed before the emphasis patterns. Moving one up turns
a URL into an italic, silently, which is why the ordering carries a comment in
the source rather than only here.

## Limits

tempest/highlight matches a flat list of regular expressions with no parser
state, which is the right trade for a highlighter but does bound what is
reachable:

- A fenced block's body is not highlighted in its own language. The fence line
  is marked; the content is left alone. (Upstream's Markdown language behaves
  the same way.)
- A block comment's body is not hidden. The `%%%` delimiters are marked, the
  lines between them are highlighted as ordinary Carve.
- Nesting depth is invisible, so a construct that means something different
  inside a container than outside it is highlighted the same either way.

For editor-grade highlighting that tracks state, use the TextMate grammar in
[carve-grammars](https://github.com/markup-carve/carve-grammars) or the
[tree-sitter grammar](https://github.com/markup-carve/tree-sitter-carve).

## Contributing

Every pattern carries `#[PatternTest]` attributes, and `tests/PatternAttributesTest.php`
runs them - upstream's runner globs its own tree, so these would otherwise never
execute. A pattern with no case fails the suite. Use `output: null` to pin what
must *not* match; those cases are the ones that catch a Markdown habit reaching
into a Carve grammar.

```bash
composer test        # phpunit
composer stan        # phpstan, level 9
composer cs-check    # phpcs
```
