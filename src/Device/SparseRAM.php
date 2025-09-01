<?php

/**
 *       _/_/_/    _/_/    _/_/_/   _/    _/  _/_/_/   _/                                                            _/
 *     _/       _/    _/  _/    _/ _/    _/  _/    _/ _/_/_/     _/_/   _/    _/   _/_/_/    _/_/_/  _/_/_/     _/_/_/
 *    _/_/_/     _/_/    _/_/_/   _/_/_/_/  _/_/_/   _/    _/ _/    _/ _/    _/ _/_/      _/    _/  _/    _/ _/    _/
 *   _/    _/ _/    _/  _/       _/    _/  _/       _/    _/ _/    _/ _/    _/     _/_/  _/    _/  _/    _/ _/    _/
 *    _/_/     _/_/    _/       _/    _/  _/       _/    _/   _/_/    _/_/_/  _/_/_/      _/_/_/  _/    _/   _/_/_/
 *
 *   >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Damn you, linkedin, what have you started ? <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 */

declare(strict_types=1);

namespace ABadCafe\G8PHPhousand\Device;

use ABadCafe\G8PHPhousand\IDevice;

use DomainException;
use ValueError;
use function str_repeat;

/**
 * Root interface for write accessible devices. All accesses are considered unsigned.
 */
class SparseRAM implements IBus
{

    private array $aBytes = [];

    private int $iPrealloc = 0;

    public function __construct(int $iPrealloc)
    {
        $this->iPrealloc = $iPrealloc;
        $this->hardReset();
    }

    public function getBaseAddress(): int
    {
        return $this->iBaseAddress;
    }

    public function getLength(): int
    {
        return $this->iLength;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'SparseRAM (array<int, int<0,255>>)';
    }

    /**
     * @inheritDoc
     */
    public function softReset(): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hardReset(): self
    {
        $this->aBytes = array_fill(0, $this->iPrealloc, 0);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readByte(int $iAddress): int
    {
        return $this->aBytes[$iAddress] ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function readWord(int $iAddress): int
    {
        return (($this->aBytes[$iAddress] ?? 0) << 8) | ($this->aBytes[$iAddress + 1] ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function readLong(int $iAddress): int
    {
        return
            (($this->aBytes[$iAddress] ?? 0) << 24) |
            (($this->aBytes[$iAddress + 1] ?? 0) << 16) |
            (($this->aBytes[$iAddress + 2] ?? 0) << 8) |
            (($this->aBytes[$iAddress + 3] ?? 0));
    }

    /**
     * @inheritDoc
     */
    public function writeByte(int $iAddress, int $iValue): void
    {
        $this->aBytes[$iAddress] = $iValue & 0xFF;
    }

    /**
     * @inheritDoc
     */
    public function writeWord(int $iAddress, int $iValue): void
    {
        $this->aBytes[$iAddress]     = ($iValue >> 8) & 0xFF;
        $this->aBytes[$iAddress + 1] = $iValue & 0xFF;
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $iAddress, int $iValue): void
    {
        $this->aBytes[$iAddress]     = ($iValue >> 24) & 0xFF;
        $this->aBytes[$iAddress + 1] = ($iValue >> 16) & 0xFF;
        $this->aBytes[$iAddress + 2] = ($iValue >> 8) & 0xFF;
        $this->aBytes[$iAddress + 3] = $iValue & 0xFF;
    }

    public function getDump($iAddress, $iLength): string
    {
        return '';
    }
}
