<?php

namespace Xterr\CpvCodes\Tests\Unit\Translation\Loader;

use PHPUnit\Framework\TestCase;
use Xterr\CpvCodes\Translation\Loader\PhpArrayLoader;

class PhpArrayLoaderTest extends TestCase
{
    /**
     * @var PhpArrayLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->loader = new PhpArrayLoader();
    }

    public function testGetAvailableLocales(): void
    {
        $locales = $this->loader->getAvailableLocales();

        static::assertIsArray($locales);
        static::assertNotEmpty($locales);
        static::assertContains('de', $locales);
        static::assertContains('fr', $locales);
        static::assertContains('es', $locales);
    }

    public function testSupportsExistingLocale(): void
    {
        static::assertTrue($this->loader->supports('de'));
        static::assertTrue($this->loader->supports('fr'));
        static::assertTrue($this->loader->supports('es'));
    }

    public function testSupportsNonExistingLocale(): void
    {
        static::assertFalse($this->loader->supports('xx'));
        static::assertFalse($this->loader->supports('invalid'));
    }

    public function testSupportsNormalizesLocale(): void
    {
        // Should normalize de_DE to de
        static::assertTrue($this->loader->supports('de_DE'));
        static::assertTrue($this->loader->supports('fr_FR'));
        static::assertTrue($this->loader->supports('es-ES'));
    }

    public function testLoadReturnsTranslations(): void
    {
        $translations = $this->loader->load('de');

        static::assertIsArray($translations);
        static::assertNotEmpty($translations);
        static::assertArrayHasKey('Lamp covers', $translations);
        static::assertEquals('Lampenabdeckungen', $translations['Lamp covers']);
    }

    public function testLoadNormalizesLocale(): void
    {
        $translations = $this->loader->load('de_DE');

        static::assertIsArray($translations);
        static::assertNotEmpty($translations);
        static::assertArrayHasKey('Lamp covers', $translations);
    }

    public function testLoadReturnsEmptyArrayForNonExistingLocale(): void
    {
        $translations = $this->loader->load('xx');

        static::assertIsArray($translations);
        static::assertEmpty($translations);
    }

    public function testLoadCachesResults(): void
    {
        // Load twice, should use cache on second call
        $translations1 = $this->loader->load('de');
        $translations2 = $this->loader->load('de');

        static::assertSame($translations1, $translations2);
    }

    public function testCustomBasePath(): void
    {
        $customPath = dirname(__DIR__, 4) . '/Resources/translations/php';
        $loader = new PhpArrayLoader($customPath);

        $translations = $loader->load('de');

        static::assertIsArray($translations);
        static::assertNotEmpty($translations);
    }

    public function testInvalidBasePathReturnsEmptyArray(): void
    {
        $loader = new PhpArrayLoader('/nonexistent/path');

        $translations = $loader->load('de');

        static::assertIsArray($translations);
        static::assertEmpty($translations);
    }
}