<?php declare(strict_types=1);

/**
 * Minimal base class for fixer tests.
 * Deliberately does not depend on PhpCsFixer\Tests\Test\AbstractFixerTestCase
 * (which is part of PHP CS Fixer's own internal test infrastructure and not
 * guaranteed to be available as a public API in third-party packages).
 *
 * File name: AbstractFixerTestCase.php
 * Created:   2026-02-27 13:24:27
 *
 * @author    Gabriel Tenita <g1704578400@tenita.eu@tenita.eu>
 * @see       https://github.com/the-ge/
 * @copyright Copyright (c) 2026-present Gabriel Tenita
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License version 2.0
 */

namespace TheGe\PhpCsFixer\Tests;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PHPUnit\Framework\TestCase;

abstract class AbstractFixerTestCase extends TestCase
{
    private FixerInterface $fixer;

    abstract protected function getFixer(): FixerInterface;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixer = $this->getFixer();
    }

    /** Asserts that the fixer does not modify correct code. */
    final protected function doSkippableTest(string $expected): void
    {
        // Code is already correct — fixer must produce no change.
        $tokens = Tokens::fromCode($expected);
        $this->fixer->fix(new \SplFileInfo('test.php'), $tokens);

        self::assertSame(
            $expected,
            $tokens->generateCode(),
            'Fixer modified already-correct code.',
        );
    }

    /** Asserts that the fixer transforms $input into $expected. */
    final protected function doFixableTest(string $expected, string $input): void
    {
        $file = new \SplFileInfo('test.php');

        // Verify the fixer actually changes the input.
        self::assertNotSame(
            $expected,
            $input,
            'Incorrect code is expected.',
        );

        $tokens = Tokens::fromCode($input);
        $this->fixer->fix($file, $tokens);

        self::assertSame(
            $expected,
            $tokens->generateCode(),
            'Fixer did not fix incorrect input.',
        );

        // Idempotency: fixing the expected output must produce no further change.
        $tokens2 = Tokens::fromCode($expected);
        $this->fixer->fix($file, $tokens2);

        self::assertSame(
            $expected,
            $tokens2->generateCode(),
            'Fixer is not idempotent: fixing expected output changed it again.',
        );
    }
}
