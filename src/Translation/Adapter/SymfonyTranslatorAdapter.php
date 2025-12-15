<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation\Adapter;

use Symfony\Contracts\Translation\LocaleAwareInterface as SymfonyLocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslatorInterface;
use Xterr\CpvCodes\Translation\LocaleAwareInterface;
use Xterr\CpvCodes\Translation\TranslatorInterface;

/**
 * Wraps Symfony's TranslatorInterface.
 * Requires: symfony/translation-contracts
 */
final class SymfonyTranslatorAdapter implements TranslatorInterface, LocaleAwareInterface
{
    /**
     * @var SymfonyTranslatorInterface|null
     */
    private $translator;

    /**
     * @param SymfonyTranslatorInterface|null $translator
     */
    public function __construct(?SymfonyTranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'cpvCodes'): string
    {
        if ($this->translator === null) {
            return $id;
        }

        return $this->translator->trans($id, [], $domain, $locale);
    }

    /**
     * @inheritDoc
     */
    public function setLocale(string $locale): void
    {
        if ($this->translator instanceof SymfonyLocaleAwareInterface) {
            $this->translator->setLocale($locale);
        }
    }

    /**
     * @inheritDoc
     */
    public function getLocale(): string
    {
        if ($this->translator instanceof SymfonyLocaleAwareInterface) {
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
