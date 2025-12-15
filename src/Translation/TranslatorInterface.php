<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Translation;

/**
 * Framework-agnostic translator interface for CPV codes.
 *
 * Adapters wrap framework-specific translators to implement this interface.
 */
interface TranslatorInterface
{
    /**
     * Translates the given message.
     *
     * @param string      $id     The message id (English text as key)
     * @param string|null $locale The locale or null to use the default
     * @param string      $domain The domain for the message
     * @return string The translated string or the original if not found
     */
    public function translate(string $id, ?string $locale = null, string $domain = 'cpvCodes'): string;
}
