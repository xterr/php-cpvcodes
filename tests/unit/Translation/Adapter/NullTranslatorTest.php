<?php

namespace Xterr\CpvCodes\Tests\Unit\Translation\Adapter;

use PHPUnit\Framework\TestCase;
use Xterr\CpvCodes\Translation\Adapter\NullTranslator;
use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

class NullTranslatorTest extends TestCase
{
    /**
     * @var NullTranslator
     */
    private $translator;

    protected function setUp(): void
    {
        $this->translator = new NullTranslator();
    }

    public function testImplementsTranslatorInterface(): void
    {
        static::assertInstanceOf(TranslatorInterface::class, $this->translator);
    }

    public function testImplementsLocaleAwareInterface(): void
    {
        static::assertInstanceOf(LocaleAwareInterface::class, $this->translator);
    }

    public function testTranslateReturnsOriginalText(): void
    {
        $text = 'Some text to translate';

        static::assertEquals($text, $this->translator->translate($text));
    }

    public function testTranslateIgnoresLocale(): void
    {
        $text = 'Some text';

        static::assertEquals($text, $this->translator->translate($text, 'de'));
        static::assertEquals($text, $this->translator->translate($text, 'fr'));
        static::assertEquals($text, $this->translator->translate($text, 'xx'));
    }

    public function testTranslateIgnoresDomain(): void
    {
        $text = 'Some text';

        static::assertEquals($text, $this->translator->translate($text, null, 'customDomain'));
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

    public function testGetAvailableLocales(): void
    {
        $locales = $this->translator->getAvailableLocales();

        static::assertIsArray($locales);
        static::assertEquals(['en'], $locales);
    }
}
