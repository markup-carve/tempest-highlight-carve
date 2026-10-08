# Changelog

All notable changes to this package are documented here.

## [Unreleased]

### Fixed

- An include directive is highlighted only where carve-php expands one. The
  pattern accepted any `{{...}}` pair, so `{{child.crv}}` and
  `{{child.crv#pick}}`, which are ordinary text, read as directives, as did a
  pair holding only whitespace. Whitespace immediately inside both braces is
  now required, which is what the engine requires.
- Heading text is separated from verbatim spans and comments: a verbatim span
  closes only on its full backtick run, a comment after multiline paragraph
  code survives, and unclosed-verbatim protection is limited to headings and
  captions.

## [0.1.0] - 2026-09-30

### Added

- Initial release: a `CarveLanguage` for tempest/highlight, registered as
  `carve` with the alias `crv`, covering the Carve core syntax across 44
  patterns.

[0.1.0]: https://github.com/markup-carve/tempest-highlight-carve/releases/tag/0.1.0
