# the-ge/phpcsfixer-fixers

Custom PHP CS Fixer fixers.

## Installation

```bash
composer require --dev the-ge/phpcsfixer-fixers
```

## Fixers

### `TheGe/classy_declaration_after_two_blank_lines`

Ensures every named classy declaration (`class`, `interface`, `trait`, `enum`) — including its modifiers (`abstract`, `final`, `readonly`) and its metadata (comments, docblock, attributes) — is preceded by exactly **two blank lines** (three newline characters).

Anonymous classes (`new class`) are excluded.

**Priority:** `-24` (runs after `blank_line_after_namespace`, `no_blank_lines_after_phpdoc`, and
`single_line_after_imports`).

#### Example

Configuration: `['blank_lines_count' => 2]`

```php
// Before
namespace N;
class Foo {}

// After
namespace N;


class Foo {}
```

#### Registration

```php
// .php-cs-fixer.dist.php
use TheGe\PhpCsFixer\Fixer\ClassNotation\ClassyDeclarationAfterTwoBlankLinesFixer;

return (new PhpCsFixer\Config())
    ->registerCustomFixers([new ClassyDeclarationAfterTwoBlankLinesFixer()])
    ->setRules([
        'TheGe/classy_declaration_after_two_blank_lines' => true,
    ]);
```

## Requirements

- PHP 8.1 – 8.4
- friendsofphp/php-cs-fixer ^3.94

## Running Tests

```bash
composer boot
composer test
```
