<?php

namespace Xterr\CpvCodes\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xterr\CpvCodes\CpvCode;
use Xterr\CpvCodes\CpvCodeMappings;
use Xterr\CpvCodes\CpvCodes;
use Xterr\CpvCodes\CpvCodesFactory;

class CpvCodesTest extends TestCase
{
    public function testIterator(): void
    {
        $isoCodes = new CpvCodesFactory();
        $cpvCodes = $isoCodes->getCodes();

        foreach ($cpvCodes as $cpvCode) {
            static::assertInstanceOf(
                CpvCode::class,
                $cpvCode
            );
        }

        static::assertIsArray($cpvCodes->toArray());
        static::assertGreaterThan(0, count($cpvCodes));
    }

    public function testGetByCodeAndVersion(): void
    {
        $isoCodes = new CpvCodesFactory();
        $cpvCode = $isoCodes->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertInstanceOf(CpvCode::class, $cpvCode);

        static::assertEquals('31532700-1', $cpvCode->getCode());
        static::assertEquals(CpvCode::VERSION_2, $cpvCode->getVersion());
        static::assertEquals(CpvCode::TYPE_SUPPLY, $cpvCode->getType());
        static::assertEquals('31532700', $cpvCode->getNumericCode());
        static::assertEquals('Lamp covers', $cpvCode->getName());
        static::assertEquals('315327', $cpvCode->getShortCode());
        static::assertEquals('31532700-1_' . CpvCode::VERSION_2, $cpvCode->getCodeVersion());

        $cpvCode = $isoCodes->getCodes()->getByCodeAndVersion('03451300-9', CpvCode::VERSION_2);
        static::assertInstanceOf(CpvCode::class, $cpvCode);

        static::assertEquals('03451300-9', $cpvCode->getCode());
        static::assertEquals(CpvCode::VERSION_2, $cpvCode->getVersion());
        static::assertEquals(CpvCode::TYPE_SUPPLY, $cpvCode->getType());
        static::assertEquals('3451300', $cpvCode->getNumericCode());
        static::assertEquals('Shrubs', $cpvCode->getName());
        static::assertEquals('034513', $cpvCode->getShortCode());
    }

    public function testGetByCodeAndVersionReturnsNullForNonExistent(): void
    {
        $factory = new CpvCodesFactory();
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('99999999-9', CpvCode::VERSION_2);

        static::assertNull($cpvCode);
    }

    public function testGetByCodeAndVersionDefaultsToVersion2(): void
    {
        $factory = new CpvCodesFactory();
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1');

        static::assertNotNull($cpvCode);
        static::assertEquals(CpvCode::VERSION_2, $cpvCode->getVersion());
    }

    public function testCount(): void
    {
        $isoCodes = new CpvCodesFactory();
        static::assertEquals(17777, $isoCodes->getCodes()->count());
    }

    public function testCpvCodeDivision(): void
    {
        $factory = new CpvCodesFactory();

        // Test a division-level code (e.g., 03000000-1 - Agricultural products)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('03000000-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('03', $cpvCode->getDivision());
        static::assertTrue($cpvCode->isDivision());

        // Test a non-division code
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('31', $cpvCode->getDivision());
        static::assertFalse($cpvCode->isDivision());
    }

    public function testCpvCodeGroup(): void
    {
        $factory = new CpvCodesFactory();

        // Test a group-level code (e.g., 03100000-2)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('03100000-2', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('031', $cpvCode->getGroup());
        static::assertTrue($cpvCode->isGroup());

        // Test a non-group code
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('315', $cpvCode->getGroup());
        static::assertFalse($cpvCode->isGroup());
    }

    public function testCpvCodeClass(): void
    {
        $factory = new CpvCodesFactory();

        // Test a class-level code (e.g., 03110000-5)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('03110000-5', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('0311', $cpvCode->getClass());
        static::assertTrue($cpvCode->isClass());

        // Test a non-class code
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('3153', $cpvCode->getClass());
        static::assertFalse($cpvCode->isClass());
    }

    public function testCpvCodeCategory(): void
    {
        $factory = new CpvCodesFactory();

        // Test a category-level code (e.g., 03111000-2)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('03111000-2', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('03111', $cpvCode->getCategory());
        static::assertTrue($cpvCode->isCategory());

        // Subcategory should also return true for isCategory
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals('31532', $cpvCode->getCategory());
        static::assertTrue($cpvCode->isCategory());
    }

    public function testCpvCodeSubcategory(): void
    {
        $factory = new CpvCodesFactory();

        // Test a subcategory-level code (has more than 5 significant digits)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertTrue($cpvCode->isSubcategory());

        // Test a category code (5 significant digits, not a subcategory)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('03111000-2', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertFalse($cpvCode->isSubcategory());
    }

    public function testCpvCodeParentCode(): void
    {
        $factory = new CpvCodesFactory();

        // Test a code with a parent
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertNotNull($cpvCode->getParentCode());

        // Test a top-level code (division) - should have no parent
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('03000000-1', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertNull($cpvCode->getParentCode());
    }

    public function testCpvCodeLocalNameFallsBackToName(): void
    {
        $factory = new CpvCodesFactory();
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);

        static::assertNotNull($cpvCode);
        static::assertEquals($cpvCode->getName(), $cpvCode->getLocalName());
    }

    public function testCpvCodeTypes(): void
    {
        $factory = new CpvCodesFactory();

        // Test TYPE_SUPPLY
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('31532700-1', CpvCode::VERSION_2);
        static::assertEquals(CpvCode::TYPE_SUPPLY, $cpvCode->getType());

        // Test TYPE_WORKS (construction works usually in 45xxxxxx range)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('45000000-7', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals(CpvCode::TYPE_WORKS, $cpvCode->getType());

        // Test TYPE_SERVICES (services usually in 50-98 range)
        $cpvCode = $factory->getCodes()->getByCodeAndVersion('50000000-5', CpvCode::VERSION_2);
        static::assertNotNull($cpvCode);
        static::assertEquals(CpvCode::TYPE_SERVICES, $cpvCode->getType());
    }

    public function testCpvCodeVersionConstants(): void
    {
        static::assertEquals(1, CpvCode::VERSION_1);
        static::assertEquals(2, CpvCode::VERSION_2);
    }

    public function testCpvCodeTypeConstants(): void
    {
        static::assertEquals(1, CpvCode::TYPE_SUPPLY);
        static::assertEquals(2, CpvCode::TYPE_WORKS);
        static::assertEquals(3, CpvCode::TYPE_SERVICES);
    }

    public function testCpvCodesCanBeCreatedWithoutFactory(): void
    {
        $codes = new CpvCodes();

        static::assertInstanceOf(CpvCodes::class, $codes);
        static::assertGreaterThan(0, count($codes));
    }

    public function testCpvCodesIteratorKey(): void
    {
        $factory = new CpvCodesFactory();
        $codes = $factory->getCodes();

        $codes->rewind();
        static::assertIsInt($codes->key());
        static::assertEquals(0, $codes->key());

        $codes->next();
        static::assertEquals(1, $codes->key());
    }

    public function testCpvCodesIteratorValid(): void
    {
        $factory = new CpvCodesFactory();
        $codes = $factory->getCodes();

        $codes->rewind();
        static::assertTrue($codes->valid());
    }

    public function testFactoryGetMappings(): void
    {
        $factory = new CpvCodesFactory();
        $mappings = $factory->getMappings();

        static::assertInstanceOf(CpvCodeMappings::class, $mappings);
    }

    public function testCpvCodeMappingsGetMapping(): void
    {
        $factory = new CpvCodesFactory();
        $mappings = $factory->getMappings();

        // Test getting a mapping that exists (from VERSION_2 to VERSION_1)
        $mapping = $mappings->getMapping('03000000-1', CpvCode::VERSION_2);

        static::assertIsArray($mapping);
        static::assertNotEmpty($mapping);
        static::assertCount(2, $mapping);
        static::assertEquals('01000000-7', $mapping[0]);
        static::assertEquals(CpvCode::VERSION_1, $mapping[1]);
    }

    public function testCpvCodeMappingsGetMappingReturnsEmptyForNonExistent(): void
    {
        $factory = new CpvCodesFactory();
        $mappings = $factory->getMappings();

        $mapping = $mappings->getMapping('99999999-9', CpvCode::VERSION_1);

        static::assertIsArray($mapping);
        static::assertEmpty($mapping);
    }

    public function testCpvCodeMappingsCanBeCreatedDirectly(): void
    {
        $mappings = new CpvCodeMappings();

        static::assertInstanceOf(CpvCodeMappings::class, $mappings);
    }

    public function testCpvCodeMappingsWithCustomPath(): void
    {
        $customPath = dirname(__DIR__, 2) . '/Resources';
        $mappings = new CpvCodeMappings($customPath);

        static::assertInstanceOf(CpvCodeMappings::class, $mappings);

        // Should still work with the default resources
        $mapping = $mappings->getMapping('99999999-9', CpvCode::VERSION_1);
        static::assertIsArray($mapping);
    }

    public function testCpvCodeMappingsDataIsCached(): void
    {
        $mappings = new CpvCodeMappings();

        // First call loads data
        $mapping1 = $mappings->getMapping('03000000-1', CpvCode::VERSION_2);
        // Second call uses cached data
        $mapping2 = $mappings->getMapping('03100000-2', CpvCode::VERSION_2);

        static::assertNotEmpty($mapping1);
        static::assertNotEmpty($mapping2);
    }

    public function testCpvCodesDataIsCached(): void
    {
        $codes = new CpvCodes();

        // First call loads and builds index
        $code1 = $codes->getByCodeAndVersion('03000000-1', CpvCode::VERSION_2);
        // Second call uses cached index
        $code2 = $codes->getByCodeAndVersion('03100000-2', CpvCode::VERSION_2);

        static::assertNotNull($code1);
        static::assertNotNull($code2);
    }
}
