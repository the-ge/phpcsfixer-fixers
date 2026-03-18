<?php declare(strict_types=1);

/**
 * PHPUnit tests for BlankLinesBeforeClassyBlockFixer
 *
 * File name: BlankLinesBeforeClassyBlockFixerTest.php
 * Created:   2026-03-03 11:27:28
 *
 * @author    Gabriel Tenita <g1704578400@tenita.eu@tenita.eu>
 * @see       https://github.com/the-ge/
 * @copyright Copyright (c) 2026-present Gabriel Tenita
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License version 2.0
 */

namespace TheGe\PhpCsFixer\Tests\Fixer\ClassNotation;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use TheGe\PhpCsFixer\Fixer\ClassNotation\BlankLinesBeforeClassyBlockFixer;
use TheGe\PhpCsFixer\Tests\AbstractFixerTestCase;


/**
 * @covers \TheGe\PhpCsFixer\Fixer\ClassNotation\BlankLinesBeforeClassyBlockFixer
 *
 * @phpstan-import-type _InputConfiguration from \TheGe\PhpCsFixer\Fixer\ClassNotation\BlankLinesBeforeClassyBlockFixer
 * @phpstan-import-type _ComputedConfiguration from \TheGe\PhpCsFixer\Fixer\ClassNotation\BlankLinesBeforeClassyBlockFixer
 */
final class BlankLinesBeforeClassyBlockFixerTest extends AbstractFixerTestCase
{
    private const SKIP         = 0;
    private const FIX          = 1;
    private const META         = 2;
    private const PHP_OPEN_TAG = ['php', '<?php'];
    private const SPACE        = ['space', '  '];

    /** @var FixerInterface&ConfigurableFixerInterface<_InputConfiguration, _ComputedConfiguration> */
    protected FixerInterface&ConfigurableFixerInterface $fixer;

    /** @return FixerInterface&ConfigurableFixerInterface<_InputConfiguration, _ComputedConfiguration> */
    protected function getFixer(): FixerInterface&ConfigurableFixerInterface
    {
        $this->fixer ??= new BlankLinesBeforeClassyBlockFixer();

        return $this->fixer;
    }

    /**
     * @param non-empty-array<string, int> $configuration
     * @param class-string<\Throwable>     $exception
     */
    #[DataProvider('provideInvalidConfigurationCases')]
    public function testInvalidConfiguration(
        array  $configuration,
        string $exceptionMessage,
        string $exception = InvalidFixerConfigurationException::class,
    ): void {
        $this->expectException($exception);
        $ruleName = BlankLinesBeforeClassyBlockFixer::RULE;
        $this->expectExceptionMessageMatches("#^\[{$ruleName}\] {$exceptionMessage}#");
        $this->getFixer()->configure($configuration);
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function provideInvalidConfigurationCases(): iterable
    {
        yield 'blank_lines_count != int' => [
            ['blank_lines_count' => true],
            'Invalid configuration: The option "blank_lines_count" with value true is expected to be of type "int", but is of type ',
        ];
        yield 'extra option' => [
            ['blank_lines_count' => 1, 'xtra_option' => 88],
            'Invalid configuration: The option "xtra_option" does not exist.',
        ];
        yield '│count < 0│' => [
            ['blank_lines_count' => BlankLinesBeforeClassyBlockFixer::LINES_MIN - 1],
            'Option `blank_lines_count` cannot be less than '.BlankLinesBeforeClassyBlockFixer::LINES_MIN,
        ];
        yield 'count > max' => [
            ['blank_lines_count' => BlankLinesBeforeClassyBlockFixer::LINES_MAX + 1],
            'Option `blank_lines_count` cannot be more than '.BlankLinesBeforeClassyBlockFixer::LINES_MAX,
        ];
    }

    /**
     * @disregard P1003 $input is not used
     *
     * @param array<string, mixed> $configuration
     */
    #[DataProvider('provideSkippableTestCases')]
    public function testSkippableCase(
        string  $expected,
        ?string $input = null,
        array   $configuration = [],
    ): void {
        $this->getFixer()->configure($configuration);
        $this->doSkippableTest($expected);
    }

    /**
     * Generate skippable cases:
     *   1. whitespace between classy declaration block and previous token is already the required one
     *   2. previous token is metadata with the correct metadata whitespace
     *   3. anonymous class
     * Iterate configurations, classy declarations, class modifiers, whitespaces, metadata types
     *
     * @return \Generator<string, array{0: string, 1: ?string, 2: array{blank_lines_count: int}}>
     */
    public static function provideSkippableTestCases(): iterable
    {
        [$precodeLabel, $precode] = self::PHP_OPEN_TAG;
        [$spaceLabel  , $space]   = self::SPACE;

        foreach (self::configurationCases() as $lineCount) {
            $configuration        = ['blank_lines_count' => $lineCount];
            $xLF                  = $lineCount + 1; // line feeds count equals line count plus 1
            [$xLFLabel, $xLFCode] = self::LF($xLF);

            // whitespace between classy declaration block and previous token is already the required one
            foreach (self::postNonMetadataCases(self::SKIP, $xLF) as $case) {
                [$description, $expected, $input] = self::codesetToSkippableCase($case, $xLF);
                yield $description => [$expected, $input, $configuration];
            }

            // the classy declaration block is preceded by metadata
            foreach (self::postMetadataCases(self::SKIP, $xLF) as $case) {
                [$description, $expected, $input] = self::codesetToSkippableCase($case, $xLF);
                yield $description => [$expected, $input, $configuration];
            }

            // anonymous class
            [$description, $expected, $input] = self::codesetToSkippableCase([
                [self::SKIP, $precodeLabel, $precode],
                [self::SKIP, $spaceLabel, $space],
                [self::SKIP, 'anonymousClass', "\$obj = new class() {};\n"],
            ], $xLF);
            yield $description => [$expected, $input, $configuration];

            // readonly anonymous class; PHP 8.3+
            [$description, $expected, $input] = self::codesetToSkippableCase([
                [self::SKIP, $precodeLabel, $precode],
                [self::SKIP, $spaceLabel, $space],
                [self::SKIP, 'readonly-anonymous-class', "\$obj = new readonly class() {};\n"],
            ], $xLF);
            yield $description => [$expected, $input, $configuration];

            // anonymous class inside named class method
            [$description, $expected, $input] = self::codesetToSkippableCase([
                [self::SKIP, $precodeLabel, $precode],
                [self::SKIP, $xLFLabel, $xLFCode],
                [self::SKIP, 'class-with-anonymous-class-inside-method', <<<PHP
                    class Outer
                    {
                        public function inner()
                        {
                            return new class(10) {
                                public function __construct(private int \$num) {}
                            };
                        }
                    }

                    PHP
                ],
            ], $xLF);
            yield $description => [$expected, $input, $configuration];
        }
    }

    /** @param array<string, mixed> $configuration */
    #[DataProvider('provideFixableTestCases')]
    public function testFixableCase(
        string $expected,
        string $input,
        array  $configuration = [],
    ): void {
        $this->getFixer()->configure($configuration);
        $this->doFixableTest($expected, $input);
    }

    /**
     * Generate fixable cases:
     *   - whitespace between classy declaration block and previous token needs fixing
     * Iterate configurations, classy declarations, class modifiers, whitespaces, metadata types
     *
     * @return \Generator<string, array{0: string, 1: ?string, 2: array{blank_lines_count: int}}>
     */
    public static function provideFixableTestCases(): iterable
    {
        [$precodeLabel, $precode] = self::PHP_OPEN_TAG;
        [$classCLabel , $classC]  = self::classDeclaration();
        [$classDLabel , $classD]  = self::classDeclaration('D');

        foreach (self::configurationCases() as $lineCount) {
            $configuration = ['blank_lines_count' => $lineCount];
            $xLF           = $lineCount + 1; // line feeds count equals line count plus 1

            // whitespace between classy declaration block and previous token needs fixing
            foreach (self::postNonMetadataCases(self::FIX, $xLF) as $case) {
                [$description, $expected, $input] = self::codesetToFixableCase($case, $xLF);
                yield $description => [$expected, $input, $configuration];
            }

            // same, but the classy declaration block is preceded by metadata
            foreach (self::postMetadataCases(self::FIX, $xLF) as $case) {
                [$description, $expected, $input] = self::codesetToFixableCase($case, $xLF);
                yield $description => [$expected, $input, $configuration];
            }

            // Edge case - two classy declarations
            foreach (self::classyGapBad($xLF) as [$gapLabel, $gap]) {
                [$description, $expected, $input] = self::codesetToFixableCase([
                    [self::SKIP, $precodeLabel, $precode],
                    [self::FIX, $gapLabel, $gap],
                    [self::SKIP, $classCLabel, $classC],
                    [self::FIX, $gapLabel, $gap],
                    [self::SKIP, $classDLabel, $classD],
                ], $xLF);
                yield $description => [$expected, $input, $configuration];
            }
        }
    }

    /**
     * Iterate cases with classy declarations preceded by non-metadata
     *
     * @return \Generator<array<array{0: int, 1: string, 2: string}>>
     */
    private static function postNonMetadataCases(int $action, int $count): iterable
    {
        foreach (($action ? self::classyGapBad($count) : [self::classyGapOk($count)]) as [$gapLabel, $gap]) {
            foreach (self::precode() as [$precodeLabel, $precode]) {
                foreach (self::declaration($gapLabel, $gap) as [$declarationLabel, $declaration]) {
                    yield [
                        [self::SKIP, $precodeLabel, $precode],
                        [$action, $gapLabel, $gap],
                        [self::SKIP, $declarationLabel, $declaration],
                    ];
                }
            }
        }
    }

    /**
     * Iterate cases with classy declarations preceded by metadata
     *
     * @return \Generator<array<array{0: int, 1: string, 2: string}>>
     */
    private static function postMetadataCases(int $action, int $count): iterable
    {
        [$precodeLabel, $precode] = [...self::PHP_OPEN_TAG];
        foreach (($action ? self::classyGapBad($count) : [self::classyGapOk($count)]) as [$classyGapLabel, $classyGap]) {
            foreach (self::metadata() as [$metadataLabel, $metadata]) {
                foreach (self::declaration($classyGapLabel, $classyGap) as [$declarationLabel, $declaration]) {
                    foreach (($action ? self::metadataGapBad() : [self::metadataGapOk()]) as [$metadataGapLabel, $metadataGap]) {
                        yield [
                            [self::SKIP, $precodeLabel, $precode],
                            [$action, $classyGapLabel, $classyGap],
                            [self::SKIP, $metadataLabel, $metadata],
                            [$action|self::META, $metadataGapLabel, $metadataGap],
                            [self::SKIP, $declarationLabel, $declaration],
                        ];
                    }
                }
            }
        }

        foreach (($action ? self::classyGapBad($count) : [self::classyGapOk($count)]) as [$classyGapLabel, $classyGap]) {
            foreach (($action ? self::metadataGapBad() : [self::metadataGapOk()]) as [$metadataGapLabel, $metadataGap]) {
                foreach (self::metadata() as [$metadata1Label, $metadata1]) {
                    foreach (self::metadata() as [$metadata2Label, $metadata2]) {
                        foreach (self::declaration($classyGapLabel, $classyGap) as [$declarationLabel, $declaration]) {
                            yield [
                                [self::SKIP, $precodeLabel, $precode],
                                [$action, $classyGapLabel, $classyGap],
                                [self::SKIP, $metadata1Label, $metadata1],
                                [$action|self::META, $metadataGapLabel, $metadataGap],
                                [self::SKIP, $metadata2Label, $metadata2],
                                [$action|self::META, $metadataGapLabel, $metadataGap],
                                [self::SKIP, $declarationLabel, $declaration],
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * @param int $xLF - line feeds (\n) count, we're building test cases around it
     *
     * @return array{0: string, 1: string}
     */
    private static function classyGapOk(int $xLF): iterable
    {
        return ["{$xLF}LF", str_repeat("\n", $xLF)];
    }

    /**
     * @param int $xLF - line feeds (\n) count, we're building test cases around it
     *
     * @return \Generator<array{0: string, 1: string}>
     */
    private static function classyGapBad(int $xLF): iterable
    {
        yield [...self::SPACE]; // if the 'no_trailing_whitespace' rule is active, this case will not occur

        if ($xLF > 1) {
            $less = $xLF - 1;
            yield ["{$less}LF", str_repeat("\n", $less)];
        }

        yield ["{$xLF}LFSpaced", str_repeat(" \n ", $xLF)];

        $more = $xLF + 1;
        yield ["{$more}LF", str_repeat("\n", $more)];
    }

    /** @return array{0: string, 1: string} */
    private static function metadataGapOk(): iterable
    {
        return ['1LF', "\n"];
    }

    /** @return \Generator<array{0: string, 1: string}> */
    private static function metadataGapBad(): iterable
    {
        yield ['2LF', "\n\n"];
        yield ['2LFSpaced', " \n \n "];
    }

    /**
     * @param int $count - line feeds count
     *
     * @return array{0: string, 1: string}
     */
    private static function LF(int $count): array
    {
        return ["{$count}LF", str_repeat("\n", $count)];
    }

    /** @return \Generator<int> */
    private static function configurationCases(): iterable
    {
        yield BlankLinesBeforeClassyBlockFixer::LINES_MIN;
        yield BlankLinesBeforeClassyBlockFixer::LINES_MIN + 1;
    }

    /** @return \Generator<array{0: string, 1: string}> */
    private static function precode(): iterable
    {
        foreach ([
            [...self::PHP_OPEN_TAG],
            ['namespace', "<?php\n\nnamespace N;"], // tokens not detected without the opening tag
        ] as [$label, $code]) {
            yield [$label, $code];
        }
    }

    /** @return \Generator<array{0: string, 1: string}> */
    private static function metadata(): iterable
    {
        foreach ([
            'attribute'   => '#[Attribute]',
            'attributeML' => "#[MultiLineAttribute([\n    'key' => 'value'\n])]",
            'comment'     => '// comment',
            'commentML'   => "/* multi-line\n  * comment\n  */",
            'docblock'    => '/** @internal  */',
            'docblockML'  => "/** @param string \$string\n *  @return bool\n */",
        ] as $label => $code) {
            yield [$label, $code];
        }
    }

    /** @return \Generator<array{0: string, 1: string}> */
    private static function modifier(): iterable
    {
        foreach ([
            ['abstract', 'abstract'],
            ['final', 'final'],
            ['readonly', 'readonly'], // PHP 8.2+
            ['final-readonly', 'final readonly'], // PHP 8.2+
        ] as [$label, $code]) {
            yield [$label, $code];
        }
    }

    /** @return \Generator<array{0: string, 1: string}> */
    private static function declaration(string $whitespaceLabel, string $whitespace): iterable
    {
        [$classLabel, $classCode] = self::classDeclaration();
        foreach ([
            [$classLabel, $classCode],
            ['interface', 'interface I {}'],
            ['trait', 'trait T {}'],
            ['enum', "enum Suit: string { case Hearts = 'H'; }"], // PHP 8.2+
        ] as [$label, $code]) {
            yield [$label, "{$code}\n"];
        }

        foreach (self::modifier() as [$modifierLabel, $modifier]) {
            $dataset = [
                [self::SKIP, $modifierLabel, $modifier],
                [self::SKIP, $whitespaceLabel, $whitespace],
                [self::SKIP, $classLabel, $classCode],
            ];
            $code = self::code($dataset);
            yield [self::description($dataset), "{$code}\n"];
        }
    }

    /**
     * @param array<array{0: int, 1: string, 2: string}> $dataset
     *
     * @return array{0: string, 1: string, 2: null}
     */
    private static function codesetToSkippableCase(array $dataset, int $xLF): array
    {
        $description = 'SKIP['.self::description($dataset)."|{$xLF}LF]";
        $fixedCode   = self::code($dataset);

        return [$description, $fixedCode, null];
    }

    /**
     * @param array<array{0: int, 1: string, 2: string}> $dataset
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private static function codesetToFixableCase(array $dataset, int $xLF): array
    {
        $description = 'FIX│'.self::description($dataset)."│{$xLF}LF";
        $inputCode   = self::code($dataset);

        $fixedSet = [];
        foreach ($dataset as $k => [$action, $label, $code]) {
            switch ($action) {
                case self::FIX:
                    preg_match('/(?<=\n)[ \t]+$/D', $code, $found);
                    $indent         = $found[0] ?? ''; // preserve indent for classy after non-metadata
                    [$label, $code] = self::classyGapOk($xLF);
                    $code .= $indent;
                    break;
                case self::FIX|self::META:
                    [$label, $code] = self::metadataGapOk();
                    break;
            }
            $fixedSet[$k] = [self::SKIP, $label, $code];
        }
        $fixedCode = self::code($fixedSet);

        return [$description, $fixedCode, $inputCode];
    }

    /** @param array<array{0: int, 1: string, 2: string}> $dataset */
    private static function description(array $dataset): string
    {
        return implode('-', array_filter(array_column($dataset, 1)));
    }

    /** @param array<array{0: int, 1: string, 2: string}> $dataset */
    private static function code(array $dataset): string
    {
        return implode('', array_filter(array_column($dataset, 2)));
    }

    /** @return array<int, string> */
    private static function classDeclaration(string $className = 'C'): array
    {
        return ['class', \sprintf('class %s {}', $className)];
    }
}
