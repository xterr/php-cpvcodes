<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation\Adapter;

use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\Loader\PhpArrayLoader;
use Xterr\CpvCodes\Translation\Loader\TranslationLoaderInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

/**
 * Native PHP translator implementation.
 * Zero external dependencies - uses PHP array files directly.
 */
final class ArrayTranslator implements TranslatorInterface, LocaleAwareInterface
{
    /**
     * @var TranslationLoaderInterface|null
     */
    private $loader;

    /**
     * @var string
     */
    private $locale = 'en';

    /**
     * @var string
     */
    private $fallbackLocale = 'en';

    /**
     * @var string|null
     */
    private $basePath;

    /**
     * @param TranslationLoaderInterface|null $loader
     * @param string|null $defaultLocale
     * @param string|null $fallbackLocale
     * @param string|null $basePath
     */
    public function __construct(
        ?TranslationLoaderInterface $loader = null,
        ?string $defaultLocale = null,
        ?string $fallbackLocale = null,
        ?string $basePath = null
    ) {
        $this->loader = $loader;
        if ($defaultLocale !== null) {
            $this->locale = $defaultLocale;
        }
        if ($fallbackLocale !== null) {
            $this->fallbackLocale = $fallbackLocale;
        }
        $this->basePath = $basePath;
    }

    /**
     * @inheritDoc
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'cpvCodes'): string
    {
        $loader = $this->getLoader();
        $targetLocale = $locale ?? $this->locale;

        // Try target locale
        $translations = $loader->load($targetLocale, $domain);
        if (isset($translations[$id])) {
            return $translations[$id];
        }

        // Try fallback locale
        if ($targetLocale !== $this->fallbackLocale) {
            $translations = $loader->load($this->fallbackLocale, $domain);
            if (isset($translations[$id])) {
                return $translations[$id];
            }
        }

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
        return $this->getLoader()->getAvailableLocales('cpvCodes');
    }

    /**
     * @param string $locale
     * @return void
     */
    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    /**
     * @return TranslationLoaderInterface
     */
    private function getLoader(): TranslationLoaderInterface
    {
        if ($this->loader === null) {
            $this->loader = new PhpArrayLoader($this->basePath);
        }
        return $this->loader;
    }
}
