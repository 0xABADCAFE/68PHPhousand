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

namespace ABadCafe\G8PHPhousand\Device\Memory;

use ABadCafe\G8PHPhousand\Device;

use DomainException;
use ValueError;

/**
 * Sparse byte array implementation. This uses a regular PHP associative array of
 * byte values that are set on first access (zero if a read).
 */
class SparseRAM implements Device\IMemory
{
    use Device\TAddressMapped;

    protected array $aBytes = [];

    public function __construct(int $iLength = (1 << 32), int $iBaseAddress = 0)
    {
        $this->iLength = $iLength;
        $this->iBaseAddress = $iBaseAddress;
        $this->hardReset();
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
        $this->aBytes = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readByte(int $iAddress): int
    {
        return $this->aBytes[$iAddress] ?? ($this->aBytes[$iAddress] = 0);
    }

    /**
     * @inheritDoc
     */
    public function readWord(int $iAddress): int
    {
        return (
            ($this->aBytes[$iAddress] ?? ($this->aBytes[$iAddress] = 0)) << 8) |
            ($this->aBytes[$iAddress + 1] ?? ($this->aBytes[$iAddress + 1] = 0)

        );
    }

    /**
     * @inheritDoc
     */
    public function readLong(int $iAddress): int
    {
        return
            (($this->aBytes[$iAddress] ?? ($this->aBytes[$iAddress] = 0)) << 24) |
            (($this->aBytes[$iAddress + 1] ?? ($this->aBytes[$iAddress + 1] = 0)) << 16) |
            (($this->aBytes[$iAddress + 2] ?? ($this->aBytes[$iAddress + 2] = 0)) << 8) |
            (($this->aBytes[$iAddress + 3] ?? ($this->aBytes[$iAddress + 3] = 0)));
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

}
