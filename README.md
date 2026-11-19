# tempest-highlight-carve

Carve 0.2 syntax highlighting for Tempest Highlight. Register `CarveLanguage` and select `carve` or `crv`.

```php
use MarkupCarve\TempestHighlight\CarveLanguage;
use Tempest\Highlight\Highlighter;

$html = (new Highlighter())->addLanguage(new CarveLanguage())->parse($source, 'carve');
```

The 0.2 scope inventory is checked by `tests/CarveLanguageTest.php`.
