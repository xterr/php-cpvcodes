<?php

namespace Xterr\CpvCodes\Tests\Unit\Translation\Adapter;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\LocaleAwareInterface as SymfonyLocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;
use Xterr\CpvCodes\Translation\Adapter\SymfonyTranslatorAdapter;
use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

class SymfonyTranslatorAdapterTest extends TestCase
{
    public function testImplementsTranslatorInterface(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        static::assertInstanceOf(TranslatorInterface::class, $adapter);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        static::assertInstanceOf(LocaleAwareInterface::class, $adapter);
    }

    public function testTranslateReturnsOriginalWhenNoTranslatorSet(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        $text = 'Some text';
        static::assertEquals($text, $adapter->translate($text));
    }

    public function testTranslateUsesSymfonyTranslator(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $symfonyTranslator
            ->expects(static::once())
            ->method('trans')
            ->with('Lamp covers', [], 'cpvCodes', 'de')
            ->willReturn('Lampenabdeckungen');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $translated = $adapter->translate('Lamp covers', 'de');

        static::assertEquals('Lampenabdeckungen', $translated);
    }

    public function testTranslateUsesDefaultDomain(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $symfonyTranslator
            ->expects(static::once())
            ->method('trans')
            ->with('Test', [], 'cpvCodes', null)
            ->willReturn('Test translated');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $adapter->translate('Test');
    }

    public function testTranslateWithCustomDomain(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);
        $symfonyTranslator
            ->expects(static::once())
            ->method('trans')
            ->with('Test', [], 'customDomain', null)
            ->willReturn('Test translated');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $adapter->translate('Test', null, 'customDomain');
    }

    public function testSetLocaleWithLocaleAwareTranslator(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyLocaleAwareTranslatorInterface::class);
        $symfonyTranslator
            ->expects(static::once())
            ->method('setLocale')
            ->with('de');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        $adapter->setLocale('de');
    }

    public function testGetLocaleWithLocaleAwareTranslator(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyLocaleAwareTranslatorInterface::class);
        $symfonyTranslator
            ->expects(static::once())
            ->method('getLocale')
            ->willReturn('fr');

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        static::assertEquals('fr', $adapter->getLocale());
    }

    public function testGetLocaleReturnsEnglishWhenNoTranslator(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        static::assertEquals('en', $adapter->getLocale());
    }

    public function testGetLocaleReturnsEnglishWhenTranslatorNotLocaleAware(): void
    {
        $symfonyTranslator = $this->createMock(SymfonyTranslatorInterface::class);

        $adapter = new SymfonyTranslatorAdapter($symfonyTranslator);

        static::assertEquals('en', $adapter->getLocale());
    }

    public function testGetAvailableLocalesReturnsEmptyArray(): void
    {
        $adapter = new SymfonyTranslatorAdapter();

        static::assertEquals([], $adapter->getAvailableLocales());
    }
}

/**
 * Combined interface for testing
 */
interface SymfonyLocaleAwareTranslatorInterface extends SymfonyTranslatorInterface, SymfonyLocaleAwareInterface
{
}
