# Changelog

All notable changes to this package are documented here.

## [Unreleased]

## [0.1.2] - 2026-10-23

### Added

- `csv` and `tsv` fence bodies are highlighted, with every unquoted field its
  own token and the separator colored apart from the fields. (#10, #11)

### Fixed

- A lone `+` is scoped as a continuation marker only where the engine reads
  one: at an open container's marker column with a non-list block below, or
  as the first block after an opener. (#13)
- A `+` glued to a quote prefix (`>+`, `>>+`, `>> +`) is paragraph text. (#14)
- Every quote marker in a line's leading run of openers is scoped, so the
  second `>` of `> > q` and the `>` of `- > q` are markers. `- > +` is scoped
  as a continuation marker where it takes the next block
  (markup-carve/carve#3421). (#15)
- A tab after a quote or list marker no longer opens one: `>\tq` and `-\t> q`
  are paragraph text, as in the engine. (#15)

## [0.1.1] - 2026-10-08
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
