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

use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Device;

use DomainException;
use ValueError;

/**
 * Sparse word array implementation. This uses a regular PHP associative array of
 * word values that are set on first access (zero if a read). All access sizes are
 * supported but the implementation is tuned for 16-bit word access.
 *
 * Alignment is silently enforced to word size.
 */
class SparseWordRAM implements Device\IMemory
{
    protected array $aWords = [];

    protected int $iSize = 0;

    public function __construct(int $iSize = (1 << 32))
    {
        $this->hardReset();
        $this->iSize = $iSize;
    }

    public function getLength(): int
    {
        return $this->iSize;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'SparseRAM (array<int, int<0,65536>>)';
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
        $this->aWords = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readByte(int $iAddress): int
    {
        $iWord = $this->readWord($iAddress);
        return ($iAddress & 1) ? ($iWord & ISize::MASK_BYTE) : (($iWord >> 8) & ISize::MASK_BYTE);
    }

    /**
     * @inheritDoc
     */
    public function readWord(int $iAddress): int
    {
        $iAddress &= 0xFFFFFFFE;
        return $this->aWords[$iAddress] ?? ($this->aWords[$iAddress] = 0);
    }

    /**
     * @inheritDoc
     */
    public function readLong(int $iAddress): int
    {
        return
            ($this->readWord($iAddress) << 16) |
            $this->readWord($iAddress + ISize::WORD);
    }

    /**
     * @inheritDoc
     */
    public function writeByte(int $iAddress, int $iValue): void
    {
        $iWord = $this->readWord($iAddress);
        if ($iAddress & 1) {
            $this->aWords[
                $iAddress & 0xFFFFFFFE
            ] = ($iWord & 0xFF00) | ($iValue & ISize::MASK_BYTE);
        } else {
            $this->aWords[$iAddress] =
                ($iWord & 0x00FF) | (($iValue & ISize::MASK_BYTE) << 8);
        }
    }

    /**
     * @inheritDoc
     */
    public function writeWord(int $iAddress, int $iValue): void
    {
        $this->aWords[$iAddress & 0xFFFFFFFE] = $iValue & ISize::MASK_WORD;
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $iAddress, int $iValue): void
    {
        $this->writeWord($iAddress, $iValue >> 16);
        $this->writeWord($iAddress + ISize::WORD, $iValue);
    }

}
