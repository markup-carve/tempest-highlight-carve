# tempest-highlight-carve

Carve 0.2 syntax highlighting for Tempest Highlight. Register `CarveLanguage` and select `carve` or `crv`.

```php
use MarkupCarve\TempestHighlight\CarveLanguage;
use Tempest\Highlight\Highlighter;

$html = (new Highlighter())->addLanguage(new CarveLanguage())->parse($source, 'carve');
```

The 0.2 scope inventory is checked by `tests/CarveLanguageTest.php`.

CSV and TSV fences receive field and separator highlighting through the built-in injections. Separator tokens use `hl-operator`; add a rule for that class if your theme does not color it. Other code-fence bodies remain unstyled under the `carve-code` class.

```css
.hl-operator { color: #c2410c; }
```

The highlighter uses flat patterns, so it cannot track arbitrary container depth or parser state. The pattern examples in `tests/PatternAttributesTest.php` cover the syntax it recognizes.
