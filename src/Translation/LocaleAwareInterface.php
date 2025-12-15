<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation;

/**
 * Interface for translators that support locale management.
 */
interface LocaleAwareInterface
{
    /**
     * Sets the current locale.
     *
     * @param string $locale
     * @return void
     */
    public function setLocale(string $locale): void;

    /**
     * Returns the current locale.
     *
     * @return string
     */
    public function getLocale(): string;

    /**
     * Returns all available locales.
     *
     * @return string[]
     */
    public function getAvailableLocales(): array;
}
