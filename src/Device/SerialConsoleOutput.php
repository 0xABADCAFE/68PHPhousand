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

/**
 * Simple terminal outputs
 */
class SerialConsoleOutput implements IBusAccessible, IAddressMapped
{
    use TAddressMapped;

    public function __construct(int $iAddress)
    {
        $this->iBaseAddress = $iAddress;
        $this->iLength = 256;
    }

    public function getName(): string
    {
        return 'Console Output';
    }

    public function softReset(): self
    {
        return $this;
    }

    public function hardReset(): self
    {
        return $this;
    }

    public function readByte(int $iAddress): int
    {
        return 0;
    }

    public function readWord(int $iAddress): int
    {
        return 0;
    }

    public function readLong(int $iAddress): int
    {
        return 0;
    }

    public function writeByte(int $iAddress, int $iValue): void
    {
        echo chr($iValue & 0xFF);
    }

    public function writeWord(int $iAddress, int $iValue): void
    {
    }

    public function writeLong(int $iAddress, int $iValue): void
    {
    }

}

