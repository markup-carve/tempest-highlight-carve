<?php

declare(strict_types=1);

namespace MarkupCarve\TempestHighlight\Test;

use MarkupCarve\TempestHighlight\CarveLanguage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\PatternTest;

/**
 * Runs every `#[PatternTest]` declared on this package's patterns.
 *
 * tempest/highlight has an equivalent runner, but it globs its OWN
 * `src/Languages/*` tree, so nothing upstream would ever execute these. A
 * pattern whose attributes are never run documents a behavior instead of
 * pinning it.
 */
final class PatternAttributesTest extends TestCase
{
    #[DataProvider('providePatternCases')]
    public function testPatternMatchesItsDeclaredExpectation(
        Pattern $pattern,
        PatternTest $case,
    ): void {
        $matches = $pattern->match($case->input);

        if ($case->output === null) {
            $this->assertSame(
                [],
                $matches['match'] ?? [],
                sprintf('%s must not match %s', $pattern::class, var_export($case->input, true)),
            );

            return;
        }

        $this->assertNotEmpty(
            $matches['match'] ?? [],
            sprintf('%s found nothing in %s', $pattern::class, var_export($case->input, true)),
        );

        $this->assertSame(
            $case->output,
            $matches['match'][0][0],
            sprintf('%s matched the wrong span in %s', $pattern::class, var_export($case->input, true)),
        );
    }

    /**
     * @return iterable<string, array{\Tempest\Highlight\Pattern, \Tempest\Highlight\PatternTest}>
     */
    public static function providePatternCases(): iterable
    {
        foreach (self::patternClasses() as $class) {
            $reflection = new ReflectionClass($class);

            foreach ($reflection->getAttributes(PatternTest::class) as $index => $attribute) {
                /** @var \Tempest\Highlight\PatternTest $case */
                $case = $attribute->newInstance();

                /** @var \Tempest\Highlight\Pattern $instance */
                $instance = $reflection->newInstance();

                yield sprintf('%s #%d', $reflection->getShortName(), $index) => [$instance, $case];
            }
        }
    }

    /**
     * Every pattern must declare at least one case; a silent pattern is an
     * untested one, and this package's whole risk is a regex that quietly
     * matches the wrong span.
     */
    public function testEveryPatternDeclaresACase(): void
    {
        $undeclared = [];

        foreach (self::patternClasses() as $class) {
            if ((new ReflectionClass($class))->getAttributes(PatternTest::class) === []) {
                $undeclared[] = $class;
            }
        }

        $this->assertSame([], $undeclared, 'every pattern needs at least one PatternTest');
    }

    /**
     * @return array<int, class-string<\Tempest\Highlight\Pattern>>
     */
    private static function patternClasses(): array
    {
        $classes = [];
        foreach ((new CarveLanguage())->getPatterns() as $pattern) {
            $class = $pattern::class;
            if (str_starts_with($class, 'MarkupCarve\\TempestHighlight\\Patterns\\')) {
                $classes[$class] = $class;
            }
        }

        self::assertNotSame([], $classes, 'no active package patterns were found');

        return array_values($classes);
    }
}
