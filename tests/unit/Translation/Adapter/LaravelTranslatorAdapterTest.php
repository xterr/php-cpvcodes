<?php

namespace Xterr\CpvCodes\Tests\Unit\Translation\Adapter;

use Illuminate\Contracts\Translation\Translator as LaravelTranslatorContract;
use PHPUnit\Framework\TestCase;
use Xterr\CpvCodes\Translation\Adapter\LaravelTranslatorAdapter;
use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

class LaravelTranslatorAdapterTest extends TestCase
{
    public function testImplementsTranslatorInterface(): void
    {
        $adapter = new LaravelTranslatorAdapter();

        static::assertInstanceOf(TranslatorInterface::class, $adapter);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        $adapter = new LaravelTranslatorAdapter();

        static::assertInstanceOf(LocaleAwareInterface::class, $adapter);
    }

    public function testTranslateReturnsOriginalWhenNoTranslatorSet(): void
    {
        $adapter = new LaravelTranslatorAdapter();

        $text = 'Some text';
        static::assertEquals($text, $adapter->translate($text));
    }

    public function testTranslateUsesLaravelTranslator(): void
    {
        $laravelTranslator = $this->createMock(LaravelTranslatorContract::class);
        $laravelTranslator
            ->expects(static::once())
            ->method('get')
            ->with('cpvcodes::cpvCodes.Lamp covers', [], 'de')
            ->willReturn('Lampenabdeckungen');

        $laravelTranslator
            ->method('getLocale')
            ->willReturn('en');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        $translated = $adapter->translate('Lamp covers', 'de');

        static::assertEquals('Lampenabdeckungen', $translated);
    }

    public function testTranslateReturnsOriginalWhenKeyNotFound(): void
    {
        $laravelTranslator = $this->createMock(LaravelTranslatorContract::class);
        $laravelTranslator
            ->method('get')
            ->willReturnCallback(function ($key) {
                // Laravel returns the key if translation not found
                return $key;
            });

        $laravelTranslator
            ->method('getLocale')
            ->willReturn('en');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        $translated = $adapter->translate('Unknown text');

        static::assertEquals('Unknown text', $translated);
    }

    public function testTranslateUsesDefaultLocale(): void
    {
        $laravelTranslator = $this->createMock(LaravelTranslatorContract::class);
        $laravelTranslator
            ->expects(static::once())
            ->method('get')
            ->with('cpvcodes::cpvCodes.Test', [], 'de')
            ->willReturn('Test translated');

        $laravelTranslator
            ->method('getLocale')
            ->willReturn('de');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        // When locale is null, uses translator's current locale
        $adapter->translate('Test');
    }

    public function testTranslateWithCustomNamespace(): void
    {
        $laravelTranslator = $this->createMock(LaravelTranslatorContract::class);
        $laravelTranslator
            ->expects(static::once())
            ->method('get')
            ->with('mypackage::cpvCodes.Test', [], 'en')
            ->willReturn('Test translated');

        $laravelTranslator
            ->method('getLocale')
            ->willReturn('en');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator, 'mypackage');

        $adapter->translate('Test');
    }

    public function testSetLocale(): void
    {
        $laravelTranslator = $this->createMock(LaravelTranslatorContract::class);
        $laravelTranslator
            ->expects(static::once())
            ->method('setLocale')
            ->with('de');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        $adapter->setLocale('de');
    }

    public function testSetLocaleWithNullTranslator(): void
    {
        $adapter = new LaravelTranslatorAdapter();

        // Should not throw
        $adapter->setLocale('de');

        static::assertTrue(true);
    }

    public function testGetLocale(): void
    {
        $laravelTranslator = $this->createMock(LaravelTranslatorContract::class);
        $laravelTranslator
            ->expects(static::once())
            ->method('getLocale')
            ->willReturn('fr');

        $adapter = new LaravelTranslatorAdapter($laravelTranslator);

        static::assertEquals('fr', $adapter->getLocale());
    }

    public function testGetLocaleReturnsEnglishWhenNoTranslator(): void
    {
        $adapter = new LaravelTranslatorAdapter();

        static::assertEquals('en', $adapter->getLocale());
    }

    public function testGetAvailableLocalesReturnsEmptyArray(): void
    {
        $adapter = new LaravelTranslatorAdapter();

        static::assertEquals([], $adapter->getAvailableLocales());
    }
}
