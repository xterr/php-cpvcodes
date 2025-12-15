<?php

namespace Xterr\CpvCodes\Tests\Unit\Translation\Adapter;

use PHPUnit\Framework\TestCase;
use Xterr\CpvCodes\Translation\Adapter\ArrayTranslator;
use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

class ArrayTranslatorTest extends TestCase
{
    /**
     * @var ArrayTranslator
     */
    private $translator;

    protected function setUp(): void
    {
        $this->translator = new ArrayTranslator();
    }

    public function testImplementsTranslatorInterface(): void
    {
        static::assertInstanceOf(TranslatorInterface::class, $this->translator);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        static::assertInstanceOf(LocaleAwareInterface::class, $this->translator);
    }

    public function testDefaultLocaleIsEnglish(): void
    {
        static::assertEquals('en', $this->translator->getLocale());
    }

    public function testSetLocale(): void
    {
        $this->translator->setLocale('de');

        static::assertEquals('de', $this->translator->getLocale());
    }

    public function testTranslateWithGermanLocale(): void
    {
        $translator = new ArrayTranslator(null, 'de');

        $translated = $translator->translate('Lamp covers');

        static::assertEquals('Lampenabdeckungen', $translated);
    }

    public function testTranslateWithFrenchLocale(): void
    {
        $translator = new ArrayTranslator(null, 'fr');

        $translated = $translator->translate('Lamp covers');

        static::assertEquals('Écran protecteur de lampe', $translated);
    }

    public function testTranslateWithExplicitLocale(): void
    {
        $translator = new ArrayTranslator(null, 'en');

        // Default locale is English, but explicitly request German
        $translated = $translator->translate('Lamp covers', 'de');

        static::assertEquals('Lampenabdeckungen', $translated);
    }

    public function testTranslateReturnsOriginalWhenNotFound(): void
    {
        $translator = new ArrayTranslator(null, 'de');

        $original = 'This text does not exist in translations';
        $translated = $translator->translate($original);

        static::assertEquals($original, $translated);
    }

    public function testTranslateWithNonExistingLocaleReturnsOriginal(): void
    {
        $translator = new ArrayTranslator(null, 'xx');

        $original = 'Lamp covers';
        $translated = $translator->translate($original);

        static::assertEquals($original, $translated);
    }

    public function testFallbackLocale(): void
    {
        // Create translator with non-existing locale but English fallback
        $translator = new ArrayTranslator(null, 'xx', 'de');

        // Should fall back to German
        $translated = $translator->translate('Lamp covers');

        static::assertEquals('Lampenabdeckungen', $translated);
    }

    public function testSetFallbackLocale(): void
    {
        $translator = new ArrayTranslator(null, 'xx');
        $translator->setFallbackLocale('fr');

        $translated = $translator->translate('Lamp covers');

        static::assertEquals('Écran protecteur de lampe', $translated);
    }

    public function testGetAvailableLocales(): void
    {
        $locales = $this->translator->getAvailableLocales();

        static::assertIsArray($locales);
        static::assertNotEmpty($locales);
        static::assertContains('de', $locales);
        static::assertContains('fr', $locales);
    }

    public function testConstructorWithCustomLocale(): void
    {
        $translator = new ArrayTranslator(null, 'es');

        static::assertEquals('es', $translator->getLocale());
    }

    public function testConstructorWithAllParameters(): void
    {
        $translator = new ArrayTranslator(null, 'de', 'en', null);

        static::assertEquals('de', $translator->getLocale());

        $translated = $translator->translate('Lamp covers');
        static::assertEquals('Lampenabdeckungen', $translated);
    }
}
