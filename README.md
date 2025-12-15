# PHP CPV Codes

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.1-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/xterr/php-cpvcodes.svg)](https://packagist.org/packages/xterr/php-cpvcodes)

A framework-agnostic PHP library for working with CPV (Common Procurement Vocabulary) codes, including built-in
translation support for 23 EU languages.

## Overview

CPV codes are a standardized classification system for public procurement in the European Union. This library provides:

- Complete CPV code database (all divisions, groups, classes, and categories)
- Support for CPV code versions 1 and 2
- Framework-agnostic translation system with 23 language support
- Adapters for Symfony, Laravel, and native PHP
- Zero runtime dependencies for the core library

## Installation

```bash
composer require xterr/php-cpvcodes
```

## Quick Start

### Basic Usage (No Translation)

```php
use Xterr\CpvCodes\CpvCodesFactory;
use Xterr\CpvCodes\CpvCode;

$factory = new CpvCodesFactory();
$codes = $factory->getCodes();

// Find a specific CPV code
$cpvCode = $codes->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

echo $cpvCode->getCode();        // "31532700-1"
echo $cpvCode->getName();        // "Lamp covers" (English)
echo $cpvCode->getLocalName();   // "Lamp covers" (falls back to English)
echo $cpvCode->getDivision();    // "31"
echo $cpvCode->getType();        // CpvCode::TYPE_SUPPLY
```

### With Translation Support

```php
use Xterr\CpvCodes\CpvCodesFactory;
use Xterr\CpvCodes\Translation\Adapter\ArrayTranslator;

// Use the built-in ArrayTranslator for zero-dependency translations
$translator = new ArrayTranslator(null, 'de');
$factory = new CpvCodesFactory(null, $translator);

$cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1');

echo $cpvCode->getName();        // "Lamp covers" (always English)
echo $cpvCode->getLocalName();   // "Lampenabdeckungen" (German translation)
```

## Translation Adapters

The library provides a framework-agnostic `TranslatorInterface` with multiple adapter implementations.

### ArrayTranslator (Native PHP - Zero Dependencies)

Best for standalone PHP applications or when you don't want any framework dependencies.

```php
use Xterr\CpvCodes\CpvCodesFactory;
use Xterr\CpvCodes\Translation\Adapter\ArrayTranslator;

// Simple usage with locale
$translator = new ArrayTranslator(null, 'fr');
$factory = new CpvCodesFactory(null, $translator);

// With fallback locale
$translator = new ArrayTranslator(null, 'fr', 'en');

// Change locale at runtime
$translator->setLocale('de');

// Get available locales
$locales = $translator->getAvailableLocales();
// ['bg', 'cs', 'da', 'de', 'el', 'es', 'et', 'fi', 'fr', 'ga', 'hr', 'hu', 'it', 'lt', 'lv', 'mt', 'nl', 'pl', 'pt', 'ro', 'sk', 'sl', 'sv']
```

### SymfonyTranslatorAdapter

For Symfony applications. Requires `symfony/translation-contracts`.

```bash
composer require symfony/translation-contracts
```

```php
use Xterr\CpvCodes\CpvCodesFactory;
use Xterr\CpvCodes\Translation\Adapter\SymfonyTranslatorAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

// In a Symfony controller or service
public function __construct(
    TranslatorInterface $symfonyTranslator
) {
    $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);
    $this->cpvCodesFactory = new CpvCodesFactory(null, $adapter);
}

// Usage
$codes = $this->cpvCodesFactory->getCodes();
$cpvCode = $codes->getByCodeAndVersion('31532700-1');

// Uses the locale from Symfony's translator (auto-detected from request)
echo $cpvCode->getLocalName();
```

#### Symfony Configuration

Copy or generate translation files to your Symfony translations directory:

```bash
# Generate YAML files for Symfony
composer translations:yaml

# Copy to your Symfony project
cp Resources/translations/cpvCodes.*.yaml /path/to/symfony/translations/
```

Or configure as a translation resource in `config/packages/translation.yaml`:

```yaml
framework:
  translator:
    paths:
      - '%kernel.project_dir%/vendor/xterr/php-cpvcodes/Resources/translations'
```

### LaravelTranslatorAdapter

For Laravel applications. Requires `illuminate/contracts`.

```bash
composer require illuminate/contracts
```

```php
use Xterr\CpvCodes\CpvCodesFactory;
use Xterr\CpvCodes\Translation\Adapter\LaravelTranslatorAdapter;
use Illuminate\Contracts\Translation\Translator;

// In a Laravel service provider
public function register()
{
    $this->app->singleton(CpvCodesFactory::class, function ($app) {
        $adapter = new LaravelTranslatorAdapter($app->make(Translator::class));
        return new CpvCodesFactory(null, $adapter);
    });
}

// Usage in controller
public function show(CpvCodesFactory $factory, string $code)
{
    $cpvCode = $factory->getCodes()->getByCodeAndVersion($code);

    // Uses Laravel's current locale
    return response()->json([
        'code' => $cpvCode->getCode(),
        'name' => $cpvCode->getName(),
        'localName' => $cpvCode->getLocalName(),
    ]);
}
```

#### Laravel Configuration

Generate and publish Laravel translation files:

```bash
# Generate Laravel PHP files
composer translations:laravel

# Copy to your Laravel project
cp -r Resources/translations/laravel/cpvcodes /path/to/laravel/lang/vendor/
```

### NullTranslator (Default)

Returns the original English text. Used internally as the default when no translator is provided.

```php
use Xterr\CpvCodes\Translation\Adapter\NullTranslator;

$translator = new NullTranslator();
echo $translator->translate('Lamp covers'); // "Lamp covers"
```

### Custom Translator

Implement the `TranslatorInterface` for custom translation sources:

```php
use Xterr\CpvCodes\Translation\TranslatorInterface;

class DatabaseTranslator implements TranslatorInterface
{
    public function translate(string $id, ?string $locale = null, string $domain = 'cpvCodes'): string
    {
        // Your custom translation logic
        return $this->repository->findTranslation($id, $locale) ?? $id;
    }
}
```

## Supported Languages

The library includes translations for 23 EU languages:

| Code | Language  | Code | Language   |
|------|-----------|------|------------|
| bg   | Bulgarian | it   | Italian    |
| cs   | Czech     | lt   | Lithuanian |
| da   | Danish    | lv   | Latvian    |
| de   | German    | mt   | Maltese    |
| el   | Greek     | nl   | Dutch      |
| es   | Spanish   | pl   | Polish     |
| et   | Estonian  | pt   | Portuguese |
| fi   | Finnish   | ro   | Romanian   |
| fr   | French    | sk   | Slovak     |
| ga   | Irish     | sl   | Slovenian  |
| hr   | Croatian  | sv   | Swedish    |
| hu   | Hungarian |      |            |

## CpvCode Properties

| Method             | Return Type | Description                             |
|--------------------|-------------|-----------------------------------------|
| `getCode()`        | `string`    | Full CPV code (e.g., "31532700-1")      |
| `getName()`        | `string`    | English name                            |
| `getLocalName()`   | `string`    | Translated name (falls back to English) |
| `getType()`        | `int`       | Type constant (SUPPLY, WORKS, SERVICES) |
| `getVersion()`     | `int`       | CPV version (1 or 2)                    |
| `getNumericCode()` | `int`       | Numeric code without check digit        |
| `getParentCode()`  | `?string`   | Parent CPV code                         |
| `getDivision()`    | `string`    | First 2 digits                          |
| `getGroup()`       | `string`    | First 3 digits                          |
| `getClass()`       | `string`    | First 4 digits                          |
| `getCategory()`    | `string`    | First 5 digits                          |
| `getShortCode()`   | `string`    | Code without trailing zeros             |

## CPV Code Types

```php
CpvCode::TYPE_SUPPLY   // 1 - Goods and supplies
CpvCode::TYPE_WORKS    // 2 - Construction works
CpvCode::TYPE_SERVICES // 3 - Services
```

## CPV Versions

```php
CpvCode::VERSION_1 // 1 - Original CPV codes
CpvCode::VERSION_2 // 2 - Updated CPV codes (2008)
```

## Iterating Over Codes

```php
$factory = new CpvCodesFactory();
$codes = $factory->getCodes();

// Iterate all codes
foreach ($codes as $cpvCode) {
    echo $cpvCode->getCode() . ': ' . $cpvCode->getLocalName() . "\n";
}

// Count total codes
echo count($codes); // ~9,454 codes

// Convert to array
$array = $codes->toArray();
```

## Building Translation Files

The library includes a console command to build translation files in different formats.

```bash
# Build all formats
composer translations:build

# Build specific formats
composer translations:php      # PHP arrays (source of truth)
composer translations:yaml     # Symfony YAML format
composer translations:laravel  # Laravel PHP format
```

Or use the console directly:

```bash
./bin/console build:translations all
./bin/console build:translations php:from-yaml
./bin/console build:translations yaml:generate
./bin/console build:translations laravel:generate
```

## API Platform Integration

For Symfony API Platform, expose `CpvCode` with the `localName` property:

```yaml
# config/api_platform/cpv_code.yaml
Xterr\CpvCodes\CpvCode:
  attributes:
    normalization_context:
      groups: [ 'cpv_code:read' ]
  properties:
    code:
      groups: [ 'cpv_code:read' ]
    name:
      groups: [ 'cpv_code:read' ]
    localName:
      groups: [ 'cpv_code:read' ]
    type:
      groups: [ 'cpv_code:read' ]
    division:
      groups: [ 'cpv_code:read' ]
```

## Requirements

- PHP >= 7.1
- ext-json

### Optional Dependencies

- `symfony/translation-contracts` - For Symfony integration
- `illuminate/contracts` - For Laravel integration
- `symfony/console` - For CLI translation builder (dev only)

## License

This library is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

- [Razvan Ceana](https://github.com/xterr) - Author
- CPV code data sourced from the [Official EU CPV](https://ted.europa.eu/en/simap/cpv)
