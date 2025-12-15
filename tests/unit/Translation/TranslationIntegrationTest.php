<?php

namespace Xterr\CpvCodes\Tests\Unit\Translation;

use PHPUnit\Framework\TestCase;
use Xterr\CpvCodes\CpvCode;
use Xterr\CpvCodes\CpvCodes;
use Xterr\CpvCodes\CpvCodesFactory;
use Xterr\CpvCodes\Translation\Adapter\ArrayTranslator;
use Xterr\CpvCodes\Translation\Adapter\NullTranslator;
use Xterr\CpvCodes\Translation\TranslatorInterface;

class TranslationIntegrationTest extends TestCase
{
    public function testCpvCodeHasLocalNameProperty(): void
    {
        $factory = new CpvCodesFactory();
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertNotNull($cpvCode);
        static::assertIsString($cpvCode->getLocalName());
    }

    public function testLocalNameFallsBackToNameWithoutTranslator(): void
    {
        $factory = new CpvCodesFactory();
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertEquals($cpvCode->getName(), $cpvCode->getLocalName());
        static::assertEquals('Lamp covers', $cpvCode->getLocalName());
    }

    public function testLocalNameWithNullTranslator(): void
    {
        $translator = new NullTranslator();
        $factory = new CpvCodesFactory(null, $translator);
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertEquals($cpvCode->getName(), $cpvCode->getLocalName());
    }

    public function testLocalNameWithGermanTranslator(): void
    {
        $translator = new ArrayTranslator(null, 'de');
        $factory = new CpvCodesFactory(null, $translator);
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertEquals('Lamp covers', $cpvCode->getName());
        static::assertEquals('Lampenabdeckungen', $cpvCode->getLocalName());
    }

    public function testLocalNameWithFrenchTranslator(): void
    {
        $translator = new ArrayTranslator(null, 'fr');
        $factory = new CpvCodesFactory(null, $translator);
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertEquals('Lamp covers', $cpvCode->getName());
        static::assertEquals('Écran protecteur de lampe', $cpvCode->getLocalName());
    }

    public function testLocalNameWithSpanishTranslator(): void
    {
        $translator = new ArrayTranslator(null, 'es');
        $factory = new CpvCodesFactory(null, $translator);
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertEquals('Lamp covers', $cpvCode->getName());
        static::assertNotEquals('Lamp covers', $cpvCode->getLocalName());
    }

    public function testIteratorWithTranslator(): void
    {
        $translator = new ArrayTranslator(null, 'de');
        $factory = new CpvCodesFactory(null, $translator);
        $codes = $factory->getCodes();

        $count = 0;
        foreach ($codes as $cpvCode) {
            static::assertInstanceOf(CpvCode::class, $cpvCode);
            static::assertIsString($cpvCode->getLocalName());

            $count++;
            if ($count >= 10) {
                break;
            }
        }

        static::assertEquals(10, $count);
    }

    public function testCpvCodesAcceptsTranslatorInterface(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->method('translate')
            ->willReturnCallback(function ($text) {
                return 'TRANSLATED: ' . $text;
            });

        $codes = new CpvCodes(null, $translator);
        $cpvCode = $codes->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertEquals('TRANSLATED: Lamp covers', $cpvCode->getLocalName());
    }

    public function testFactoryPassesTranslatorToCpvCodes(): void
    {
        $translator = new ArrayTranslator(null, 'de');
        $factory = new CpvCodesFactory(null, $translator);

        $codes1 = $factory->getCodes();
        $codes2 = $factory->getCodes();

        $cpvCode1 = $codes1->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        $cpvCode2 = $codes2->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertEquals($cpvCode1->getLocalName(), $cpvCode2->getLocalName());
        static::assertEquals('Lampenabdeckungen', $cpvCode1->getLocalName());
    }

    public function testDifferentLocalesProduceDifferentTranslations(): void
    {
        $germanTranslator = new ArrayTranslator(null, 'de');
        $frenchTranslator = new ArrayTranslator(null, 'fr');

        $germanFactory = new CpvCodesFactory(null, $germanTranslator);
        $frenchFactory = new CpvCodesFactory(null, $frenchTranslator);

        $germanCode = $germanFactory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        $frenchCode = $frenchFactory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertNotEquals($germanCode->getLocalName(), $frenchCode->getLocalName());
        static::assertEquals('Lampenabdeckungen', $germanCode->getLocalName());
        static::assertEquals('Écran protecteur de lampe', $frenchCode->getLocalName());
    }

    public function testFactoryWithNullTranslatorUsesDefault(): void
    {
        $factory = new CpvCodesFactory(null, null);
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        // Should use NullTranslator by default, which returns original text
        static::assertEquals('Lamp covers', $cpvCode->getLocalName());
    }

    public function testAllConstructorArgumentsAreOptional(): void
    {
        // Factory
        $factory = new CpvCodesFactory();
        static::assertInstanceOf(CpvCodesFactory::class, $factory);

        // CpvCodes
        $codes = new CpvCodes();
        static::assertInstanceOf(CpvCodes::class, $codes);

        // ArrayTranslator
        $translator = new ArrayTranslator();
        static::assertInstanceOf(ArrayTranslator::class, $translator);

        // NullTranslator
        $nullTranslator = new NullTranslator();
        static::assertInstanceOf(NullTranslator::class, $nullTranslator);
    }
}
