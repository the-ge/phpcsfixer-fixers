<?php declare(strict_types=1);

/**
 * Provides fixers collection for registering in configurations
 *
 * File name: Fixers.php
 * Created:   2026-03-18 14:50:41
 *
 * @author    Gabriel Tenita <g1704578400@tenita.eu@tenita.eu>
 * @see      https://github.com/the-ge/
 * @copyright Copyright (c) 2026-present Gabriel Tenita
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License version 2.0
 */

namespace TheGe\PhpCsFixer;

use PhpCsFixer\Fixer\FixerInterface;


/**
 * @implements \IteratorAggregate<FixerInterface>
 *
 * @no-named-arguments
 */
final class Fixers implements \IteratorAggregate
{
    /**
     * @return \Generator<FixerInterface>
     */
    public function getIterator(): \Generator
    {
        $classes = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            __DIR__.'/Fixer',
            \RecursiveDirectoryIterator::SKIP_DOTS
        )) as $node) {
            if ($node->getExtension() !== 'php') {
                continue;
            }

            $name = $node->getBasename('.php');

            if (
                $name === 'AbstractFixer'
                || !str_ends_with($name, 'Fixer')
            ) {
                continue;
            }

            /**
             * Trim leading __DIR__ (current path) and trailing '.php' from the node path,
             * then convert slashes to backslashes and prefix it with __NAMESPACE__ (current namespace)
             *
             * @var class-string<FixerInterface>
             */
            $class = __NAMESPACE__.str_replace('/', '\\', mb_substr($node->getPathname(), mb_strlen(__DIR__), -4));

            if (!class_exists($class)) {
                continue;
            }

            $classes[] = $class;
        }

        foreach ($classes as $class) {
            yield new $class();
        }
    }
}
