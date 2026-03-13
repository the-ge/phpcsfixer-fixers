<?php declare(strict_types=1);

/**
 * Collapses all the metadata before non-anonymous classy declaration blocks
 * (consisting of a class/interface/trait/enum keyword together with
 * - if it's a class - all its class modifiers, and its metadata - comments, docblocks, and attributes),
 * then enforces a configurable number of blank lines before it,
 * unless the whole classy declaration block is preceded by a curly bracket.
 *
 * File name: BlankLinesBeforeClassyBlockFixer.php
 * Created:   2026-03-03 08:40:33
 *
 * @author    Gabriel Tenita <g1704578400@tenita.eu@tenita.eu>
 * @see       https://github.com/the-ge/
 * @copyright Copyright (c) 2026-present Gabriel Tenita
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License version 2.0
 */

namespace TheGe\PhpCsFixer\Fixer\ClassNotation;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConfigurableFixerTrait;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\FCT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use Symfony\Component\OptionsResolver\Options;
use TheGe\PhpCsFixer\Fixer\AbstractFixer;

/**
 * @phpstan-type _InputConfiguration array{blank_lines_count?: int}
 * @phpstan-type _ComputedConfiguration array{blank_lines_count: int}
 *
 * @implements ConfigurableFixerInterface<_InputConfiguration, _ComputedConfiguration>
 *
 * @no-named-arguments
 */
final class BlankLinesBeforeClassyBlockFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /** @use ConfigurableFixerTrait<_InputConfiguration, _ComputedConfiguration> */
    use ConfigurableFixerTrait;

    public const RULE       = 'TheGe/blank_lines_before_classy_block';
    public const CONFIG     = 'blank_lines_count';
    public const PRIORITY   = -24;
    public const LINES_MIN  = 0; // min 1 line feed
    public const LINES_OK   = 2; // 3 line feeds
    public const LINES_MAX  = 64; // max 65 line feeds
    private const CLASSY    = [\T_CLASS, \T_INTERFACE, \T_TRAIT, FCT::T_ENUM];
    private const MODIFIERY = [\T_ABSTRACT, \T_FINAL, FCT::T_READONLY];
    private const METADATY  = [\T_COMMENT, \T_DOC_COMMENT, CT::T_ATTRIBUTE_CLOSE];

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Classy declarations (class, interface, trait, enum) must be preceded by exactly three newlines (two blank lines) unless immediately preceded by metadata (comment, docblock, or attribute).',
            [
                new CodeSample("<?php\n\namespace N;\nclass C {}\n"),
                new CodeSample("<?php\n\namespace N;\n\n\n\nclass C {}\n"),
            ],
        );
    }

    protected function createConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder(self::CONFIG, 'The desired count of blank lines before named classy declaration and its metadata.'))
                ->setAllowedTypes(['int'])
                ->setDefault(self::LINES_OK)
                ->setNormalizer(static function(Options $options, int $value): int {
                    if ($value < self::LINES_MIN) {
                        throw new InvalidFixerConfigurationException(
                            (new self())->getName(),
                            'Option `blank_lines_count` cannot be less than '.self::LINES_MIN.'.',
                        );
                    } elseif ($value > self::LINES_MAX) {
                        throw new InvalidFixerConfigurationException(
                            (new self())->getName(),
                            'Option `blank_lines_count` cannot be more than '.self::LINES_MAX.' (arbitrarily chosen value, but hey...).',
                        );
                    }

                    return $value;
                })
                ->getOption(),
        ]);
    }

    public function getPriority(): int
    {
        // Must run AFTER:
        //   - class_definition             ( 36)
        //   - no_trailing_whitespace       (  0)
        //   - single_line_after_imports    (-11)
        //   - blank_line_after_namespace   (-20)
        return self::PRIORITY;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound(self::CLASSY);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        /** @var int $blankLinesCount */
        $blankLinesCount = $this->configuration[self::CONFIG] ?? self::LINES_OK;
        $fixedLFCount = $blankLinesCount + 1;

        // Iterate backwards so that any token insertions do not shift indexes of
        // declarations that have not been processed yet.
        /** @var int<0, max> $fixIndex */
        foreach (array_reverse($this->getClassyIndexes($tokens)) as $fixIndex) {
            if ($fixIndex === 0) { // we are already at the top
                break; // exit before doing anything
            }

            while ($fixIndex > 0) {
                $prevIndex = $tokens->getPrevNonWhitespace($fixIndex);

                if ($prevIndex === null) { // only whitespace found before the classy declaration
                    break 2; // we reached the code beginning, exit before doing anything
                }

                $prevToken = $tokens[$prevIndex];

                if ($prevToken->isGivenKind(\T_NEW)) {
                    continue 2; // anonymous class declaration found, skip to next declaration
                }

                if ($prevToken->getContent() === '{') {
                    continue 2; // classy declaration is after a curly bracket, skip to next declaration
                }

                if ($prevToken->isGivenKind(self::MODIFIERY)) {
                    $fixIndex = $prevIndex;
                    continue; // modifier found, move fixIndex and continue searching upstream
                }

                if ($prevToken->isGivenKind(self::METADATY)) { // preceding metadata found
                    if ($fixIndex - $prevIndex > 1) { // there is whitespace between these two indexes
                        $tokens[$fixIndex - 1] = new Token([\T_WHITESPACE, "\n"]); // normalize whitespace
                    }
                    $fixIndex = $prevToken->isGivenKind(CT::T_ATTRIBUTE_CLOSE) // if metadta is an attribute
                        ? $tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $prevIndex) // find attribute start
                        : $prevIndex;
                    continue; // move fixIndex and continue searching upstream
                }

                break; // non-classy, non-metadaty, non-whitespace token found at prevIndex
            }

            $this->enforceRule($tokens, $fixIndex, $prevIndex, $fixedLFCount);
        }
    }

    private function enforceRule(Tokens &$tokens, int $fixIndex, int $prevNonWSIndex, int $fixedLFCount): void
    {
        $newWhitespace = fn(string $content): Token => new Token([\T_WHITESPACE, $content]);
        $hasGap        = $fixIndex - $prevNonWSIndex > 1;
        $fixedContent  = str_repeat("\n", $fixedLFCount);
        $prevContent   = $tokens[$prevNonWSIndex]->getContent();

        // if prev has trailing whitespace, trim it
        if (preg_match('/\s+$/', $prevContent) === 1) {
            $prevContent = rtrim($prevContent);
            $kind        = $tokens[$prevNonWSIndex]->getName();
            $tokens->offsetSet($prevNonWSIndex, new Token(
                $kind === null
                    ? $prevContent
                    : [\constant($kind), $prevContent]
            ));
        }

        if (!$hasGap) { // no whitespace preceding fixIndex, so insert a correct one
            $tokens->insertSlices([
                $fixIndex => $newWhitespace($fixedContent),
            ]);

            return;
        }

        // there is whitespace at fixIndex -1, so fix it
        $prevContent = $tokens[$fixIndex - 1]->getContent();
        $prevLFCount = substr_count($prevContent, "\n");

        preg_match('/(?<=\n)[ \t]+$/D', $prevContent, $found);
        $indent = $found[0] ?? ''; // preserve indent

        // if the whitespace has the required new lines and no indent, do nothing
        if ($prevLFCount === $fixedLFCount && $indent === '') {
            return;
        }

        $tokens->offsetSet($fixIndex - 1, $newWhitespace($fixedContent.$indent));
    }

    /**
     * Return token indexes of all classy declarations.
     *
     * @return list<int>
     */
    protected function getClassyIndexes(Tokens $tokens): array
    {
        $indexes = [];

        foreach ($tokens as $index => $token) {
            if (!$token->isGivenKind(self::CLASSY)) {
                continue;
            }

            $indexes[] = $index;
        }

        return $indexes;
    }
}
