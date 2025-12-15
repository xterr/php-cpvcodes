<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation\Loader;

/**
 * Loads translations from PHP array files.
 * Zero external dependencies - uses native PHP only.
 */
final class PhpArrayLoader implements TranslationLoaderInterface
{
    /**
     * @var string|null
     */
    private $basePath;

    /**
     * @var array<string, array<string, string>>
     */
    private $loaded = [];

    /**
     * @var array<string, string[]>
     */
    private $availableLocales = [];

    /**
     * @param string|null $basePath
     */
    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath;
    }

    /**
     * @inheritDoc
     */
    public function load(string $locale, string $domain = 'cpvCodes'): array
    {
        $cacheKey = "{$domain}.{$locale}";

        if (isset($this->loaded[$cacheKey])) {
            return $this->loaded[$cacheKey];
        }

        $file = $this->getFilePath($locale, $domain);

        if (!file_exists($file)) {
            $normalizedLocale = $this->normalizeLocale($locale);
            if ($normalizedLocale !== $locale) {
                $file = $this->getFilePath($normalizedLocale, $domain);
            }
        }

        if (!file_exists($file)) {
            return $this->loaded[$cacheKey] = [];
        }

        $translations = require $file;

        return $this->loaded[$cacheKey] = is_array($translations) ? $translations : [];
    }

    /**
     * @inheritDoc
     */
    public function supports(string $locale, string $domain = 'cpvCodes'): bool
    {
        return in_array(
            $this->normalizeLocale($locale),
            $this->getAvailableLocales($domain),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLocales(string $domain = 'cpvCodes'): array
    {
        if (isset($this->availableLocales[$domain])) {
            return $this->availableLocales[$domain];
        }

        $pattern = $this->getBasePath() . "/{$domain}.*.php";
        $files = glob($pattern) ?: [];

        $this->availableLocales[$domain] = array_map(
            function (string $file) use ($domain): string {
                return $this->extractLocale($file, $domain);
            },
            $files
        );

        return $this->availableLocales[$domain];
    }

    /**
     * @return string
     */
    private function getBasePath(): string
    {
        return $this->basePath ?? dirname(__DIR__, 3) . '/Resources/translations/php';
    }

    /**
     * @param string $locale
     * @param string $domain
     * @return string
     */
    private function getFilePath(string $locale, string $domain): string
    {
        return $this->getBasePath() . "/{$domain}.{$locale}.php";
    }

    /**
     * @param string $locale
     * @return string
     */
    private function normalizeLocale(string $locale): string
    {
        return strtolower(explode('_', str_replace('-', '_', $locale))[0]);
    }

    /**
     * @param string $filePath
     * @param string $domain
     * @return string
     */
    private function extractLocale(string $filePath, string $domain): string
    {
        $filename = basename($filePath, '.php');
        return str_replace("{$domain}.", '', $filename);
    }
}
