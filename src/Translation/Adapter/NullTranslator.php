<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation\Adapter;

use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

/**
 * Returns original text without translation.
 * Use when translations are not needed or unavailable.
 */
final class NullTranslator implements TranslatorInterface, LocaleAwareInterface
{
    /**
     * @var string
     */
    private $locale = 'en';

    /**
     * @inheritDoc
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'cpvCodes'): string
    {
        return $id;
    }

    /**
     * @inheritDoc
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLocales(): array
    {
        return ['en'];
    }
}
