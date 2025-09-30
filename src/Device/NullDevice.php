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
 * Null device. All reads return zero, all writes are no-op
 */
class NullDevice implements IBus
{
    public function getName(): string
    {
        return 'Null Device';
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
    }

    public function writeWord(int $iAddress, int $iValue): void
    {
    }

    public function writeLong(int $iAddress, int $iValue): void
    {
    }

}

