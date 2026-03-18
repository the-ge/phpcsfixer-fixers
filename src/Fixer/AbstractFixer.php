<?php declare(strict_types=1);

/**
 * [TODO] add description
 *
 * File name: AbstractFixer.php
 * Created:   2026-03-07 07:53:39
 *
 * @author    Gabriel Tenita <g1704578400@tenita.eu@tenita.eu>
 * @see       https://github.com/the-ge/
 * @copyright Copyright (c) 2026-present Gabriel Tenita
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License version 2.0
 */

namespace TheGe\PhpCsFixer\Fixer;

use PhpCsFixer\ConfigurationException\RequiredFixerConfigurationException;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\WhitespacesFixerConfig;


abstract class AbstractFixer implements FixerInterface
{
    protected const RULE = '';

    protected readonly string $name; // readonly properties PHP 8.1+

    /** @var ?array<string, bool|int|string> */
    protected ?array $configuration = null;

    protected WhitespacesFixerConfig $whitespacesConfig;

    public function __construct()
    {
        $this->name = $this::RULE;

        if ($this->name === '') {
            throw new \ValueError('Fixer name must not be empty');
        }

        if ($this instanceof ConfigurableFixerInterface) {
            /** @disregard P1003 $e not used */
            try {
                $this->configure([]);
            } catch (RequiredFixerConfigurationException $e) {
            }
        }

        if ($this instanceof WhitespacesAwareFixerInterface) {
            $this->whitespacesConfig = $this->getDefaultWhitespacesFixerConfig();
        }
    }

    /**
     * Check if the fixer is a candidate for given Tokens collection.
     *
     * Fixer is a candidate when the collection contains tokens that may be fixed
     * during fixer work. This could be considered as some kind of bloom filter.
     * When this method returns true then to the Tokens collection may or may not
     * need a fixing, but when this method returns false then the Tokens collection
     * need no fixing for sure.
     */
    abstract public function isCandidate(Tokens $tokens): bool;

    public function isRisky(): bool
    {
        return false;
    }

    final public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        if ($this instanceof ConfigurableFixerInterface && null === $this->configuration) {
            throw new RequiredFixerConfigurationException($this->getName(), 'Configuration is required.');
        }

        if (0 < $tokens->count() && $this->isCandidate($tokens) && $this->supports($file)) {
            $this->applyFix($file, $tokens);
        }
    }

    abstract protected function applyFix(\SplFileInfo $file, Tokens $tokens): void;

    abstract public function getDefinition(): FixerDefinitionInterface;

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function supports(\SplFileInfo $file): bool
    {
        return true;
    }

    protected function getDefaultWhitespacesFixerConfig(): WhitespacesFixerConfig
    {
        static $defaultWhitespacesFixerConfig = null;

        if (null === $defaultWhitespacesFixerConfig) {
            $defaultWhitespacesFixerConfig = new WhitespacesFixerConfig('    ', "\n");
        }

        return $defaultWhitespacesFixerConfig;
    }
}
