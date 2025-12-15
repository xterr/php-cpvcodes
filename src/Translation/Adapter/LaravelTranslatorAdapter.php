<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation\Adapter;

use Illuminate\Contracts\Translation\Translator as LaravelTranslatorContract;
use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

/**
 * Wraps Laravel's Translator.
 * Requires: illuminate/contracts
 */
final class LaravelTranslatorAdapter implements TranslatorInterface, LocaleAwareInterface
{
    /**
     * @var LaravelTranslatorContract|null
     */
    private $translator;

    /**
     * @var string
     */
    private $namespace = 'cpvcodes';

    /**
     * @param LaravelTranslatorContract|null $translator
     * @param string|null $namespace
     */
    public function __construct(?LaravelTranslatorContract $translator = null, ?string $namespace = null)
    {
        $this->translator = $translator;
        if ($namespace !== null) {
            $this->namespace = $namespace;
        }
    }

    /**
     * @inheritDoc
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'cpvCodes'): string
    {
        if ($this->translator === null) {
            return $id;
        }

        $key = "{$this->namespace}::{$domain}.{$id}";
        $translated = $this->translator->get($key, [], $locale ?? $this->getLocale());

        return $translated === $key ? $id : $translated;
    }

    /**
     * @inheritDoc
     */
    public function setLocale(string $locale): void
    {
        if ($this->translator !== null) {
            $this->translator->setLocale($locale);
        }
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string
    {
        if ($this->translator !== null) {
            return $this->translator->getLocale();
        }

        return 'en';
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLocales(): array
    {
        return [];
    }
}
