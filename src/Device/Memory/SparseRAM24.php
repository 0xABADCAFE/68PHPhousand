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
 * byte values that are set on first access (zero if a read). This implementation
 * uses 24-bit address masking with full wraparound. This is intended for use with
 * test harnesses that expect 24 bit memory behaviours, e.g. the Tom Harte tests.
 */
class SparseRAM24 implements Device\IMemory
{
    protected array $aBytes = [];

    public function __construct()
    {
        $this->hardReset();
    }

    public function getBaseAddress(): int
    {
        return 0;
    }

    public function getLength(): int
    {
        return 1<<24;
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
        $iAddress &= 0x00FFFFFF;
        return $this->aBytes[$iAddress] ?? ($this->aBytes[$iAddress] = 0);
    }

    /**
     * @inheritDoc
     */
    public function readWord(int $iAddress): int
    {
        $iAddress &= 0x00FFFFFF;
        $iAddress1 = ($iAddress + 1) & 0x00FFFFFF;
        return (
            ($this->aBytes[$iAddress] ?? ($this->aBytes[$iAddress] = 0)) << 8) |
            ($this->aBytes[$iAddress1] ?? ($this->aBytes[$iAddress1] = 0)

        );
    }

    /**
     * @inheritDoc
     */
    public function readLong(int $iAddress): int
    {
        $iAddress &= 0x00FFFFFF;
        $iAddress1 = ($iAddress + 1) & 0x00FFFFFF;
        $iAddress2 = ($iAddress + 2) & 0x00FFFFFF;
        $iAddress3 = ($iAddress + 3) & 0x00FFFFFF;

        return
            (($this->aBytes[$iAddress]  ?? ($this->aBytes[$iAddress]  = 0)) << 24) |
            (($this->aBytes[$iAddress1] ?? ($this->aBytes[$iAddress1] = 0)) << 16) |
            (($this->aBytes[$iAddress2] ?? ($this->aBytes[$iAddress2] = 0)) << 8) |
            (($this->aBytes[$iAddress3] ?? ($this->aBytes[$iAddress3] = 0)));
    }

    /**
     * @inheritDoc
     */
    public function writeByte(int $iAddress, int $iValue): void
    {
        $iAddress &= 0x00FFFFFF;
        $this->aBytes[$iAddress] = $iValue & 0xFF;
    }

    /**
     * @inheritDoc
     */
    public function writeWord(int $iAddress, int $iValue): void
    {
        $iAddress &= 0x00FFFFFF;
        $iAddress1 = ($iAddress + 1) & 0x00FFFFFF;
        $this->aBytes[$iAddress]  = ($iValue >> 8) & 0xFF;
        $this->aBytes[$iAddress1] = $iValue & 0xFF;
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $iAddress, int $iValue): void
    {
        $iAddress &= 0x00FFFFFF;
        $iAddress1 = ($iAddress + 1) & 0x00FFFFFF;
        $iAddress2 = ($iAddress + 2) & 0x00FFFFFF;
        $iAddress3 = ($iAddress + 3) & 0x00FFFFFF;
        $this->aBytes[$iAddress]  = ($iValue >> 24) & 0xFF;
        $this->aBytes[$iAddress1] = ($iValue >> 16) & 0xFF;
        $this->aBytes[$iAddress2] = ($iValue >> 8) & 0xFF;
        $this->aBytes[$iAddress3] = $iValue & 0xFF;
    }

}
