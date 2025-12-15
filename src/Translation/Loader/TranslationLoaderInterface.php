<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation\Loader;

/**
 * Interface for loading translation data.
 */
interface TranslationLoaderInterface
{
    /**
     * Loads translations for a specific locale.
     *
     * @param string $locale The locale code (e.g., 'de', 'fr')
     * @param string $domain The translation domain
     * @return array<string, string> Key-value pairs of translations
     */
    public function load(string $locale, string $domain = 'cpvCodes'): array;

    /**
     * Checks if translations exist for a locale.
     *
     * @param string $locale
     * @param string $domain
     * @return bool
     */
    public function supports(string $locale, string $domain = 'cpvCodes'): bool;

    /**
     * Returns all available locales.
     *
     * @param string $domain
     * @return string[]
     */
    public function getAvailableLocales(string $domain = 'cpvCodes'): array;
}
