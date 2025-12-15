<?php

namespace Xterr\CpvCodes;

use Xterr\CpvCodes\Translation\TranslatorInterface;

class CpvCodesFactory
{
    /**
     * @var string|null
     */
    private $baseDirectory;

    /**
     * @var TranslatorInterface|null
     */
    private $translator;

    /**
     * @param string|null $baseDirectory
     * @param TranslatorInterface|null $translator
     */
    public function __construct(?string $baseDirectory = null, ?TranslatorInterface $translator = null)
    {
        $this->baseDirectory = $baseDirectory;
        $this->translator    = $translator;
    }

    /**
     * @return CpvCodes
     */
    public function getCodes(): CpvCodes
    {
        return new CpvCodes($this->baseDirectory, $this->translator);
    }

    /**
     * @return CpvCodeMappings
     */
    public function getMappings(): CpvCodeMappings
    {
        return new CpvCodeMappings($this->baseDirectory);
    }
}
