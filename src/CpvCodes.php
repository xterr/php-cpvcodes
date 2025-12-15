<?php

namespace Xterr\CpvCodes;

use Closure;
use Countable;
use Iterator;
use Xterr\CpvCodes\Translation\Adapter\NullTranslator;
use Xterr\CpvCodes\Translation\TranslatorInterface;

/**
 * @template-implements Iterator<int, CpvCode>
 */
class CpvCodes implements Iterator, Countable
{
    /**
     * @var string
     */
    private $baseDirectory;

    /**
     * @var TranslatorInterface|null
     */
    private $translator;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $index = [];

    /**
     * @param string|null $baseDirectory
     * @param TranslatorInterface|null $translator
     */
    public function __construct(?string $baseDirectory = null, ?TranslatorInterface $translator = null)
    {
        $this->baseDirectory = $baseDirectory ?? __DIR__ . '/../Resources';
        $this->translator    = $translator;
    }

    /**
     * @param string $code
     * @param int $version
     * @return CpvCode|null
     */
    public function getByCodeAndVersion(string $code, int $version = CpvCode::VERSION_2): ?CpvCode
    {
        return $this->_find('code_version', [$code, $version]);
    }

    /**
     * @return CpvCode
     */
    public function current(): CpvCode
    {
        return $this->arrayToEntry(current($this->data));
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @return int|null
     */
    public function key(): ?int
    {
        return key($this->data);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->key() !== null;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->_loadData();
        reset($this->data);
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator(): TranslatorInterface
    {
        if ($this->translator === null) {
            $this->translator = new NullTranslator();
        }
        return $this->translator;
    }

    /**
     * @param array $entry
     * @return CpvCode
     */
    protected function arrayToEntry(array $entry): CpvCode
    {
        $translator = $this->getTranslator();
        $closure    = Closure::bind(static function () use ($entry, $translator) {
            $cpvCode              = new CpvCode();
            $cpvCode->name        = $entry['name'];
            $cpvCode->localName   = $translator->translate($entry['name']);
            $cpvCode->code        = $entry['code'];
            $cpvCode->type        = $entry['type'];
            $cpvCode->numericCode = $entry['numericCode'];
            $cpvCode->version     = $entry['version'];
            $cpvCode->parentCode  = $entry['parent'];
            $cpvCode->codeVersion = implode('_', [$entry['code'], $entry['version']]);

            return $cpvCode;
        }, null, CpvCode::class);

        return $closure();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        $this->_loadData();

        return count($this->data);
    }

    /**
     * @return array<int, CpvCode>
     */
    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    /**
     * @return void
     */
    private function _loadData(): void
    {
        if (!empty($this->data)) {
            return;
        }

        $this->data = json_decode(file_get_contents($this->baseDirectory . DIRECTORY_SEPARATOR .
            'cpvCodes.json'), true);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return CpvCode|null
     */
    private function _find(string $key, $value): ?CpvCode
    {
        $this->_buildIndex();

        return $this->index[$key][is_array($value) ? implode('_', $value) : $value] ?? null;
    }

    /**
     * @return void
     */
    private function _buildIndex(): void
    {
        if (!empty($this->index)) {
            return;
        }

        $this->_loadData();

        foreach ($this->data as $entry) {
            $this->index['code_version'][implode('_', [
                $entry['code'], $entry['version'],
            ])] = $this->arrayToEntry($entry);
        }
    }
}
