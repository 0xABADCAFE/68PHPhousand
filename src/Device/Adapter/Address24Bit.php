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

namespace ABadCafe\G8PHPhousand\Device\Adapter;

use ABadCafe\G8PHPhousand\Device\IBusAccessible;

/**
 * 24-bit address truncation
 */
class Address24Bit implements IBusAccessible
{
    private IBusAccessible $oDevice;

    private const ADDR_MASK = 0x00FFFFFF;

    public function __construct(IBusAccessible $oDevice)
    {
        $this->oDevice = $oDevice;
    }

    public function getName(): string
    {
        return '24-bit addressable ' . $this->oDevice->getName();
    }

    public function softReset(): self
    {
        $this->oDevice->softReset();
        return $this;
    }

    public function hardReset(): self
    {
        $this->oDevice->hardReset();
        return $this;
    }

    public function readByte(int $iAddress): int
    {
        return $this->oDevice->readByte($iAddress & self::ADDR_MASK);
    }

    public function readWord(int $iAddress): int
    {
        return $this->oDevice->readWord($iAddress & self::ADDR_MASK);
    }

    public function readLong(int $iAddress): int
    {
        return $this->oDevice->readLong($iAddress & self::ADDR_MASK);
    }

    public function writeByte(int $iAddress, int $iValue): void
    {
        $this->oDevice->writeByte($iAddress & self::ADDR_MASK, $iValue);
    }

    public function writeWord(int $iAddress, int $iValue): void
    {
        $this->oDevice->writeWord($iAddress & self::ADDR_MASK, $iValue);
    }

    public function writeLong(int $iAddress, int $iValue): void
    {
        $this->oDevice->writeLong($iAddress & self::ADDR_MASK, $iValue);
    }

}

