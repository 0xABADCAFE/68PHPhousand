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

namespace ABadCafe\G8PHPhousand\Processor\EAMode;

use ABadCafe\G8PHPhousand\Device;
use ABadCafe\G8PHPhousand\Processor;

/**
 * For modes that have extension words, provides the functionality required to read from
 * the program counter.
 */
trait TWithExtensionWords
{
    use TWithBusAccess;

    protected int $iProgramCounter;

    protected function bindProgramCounter(int& $iProgramCounter)
    {
        $this->iProgramCounter = &$iProgramCounter;
    }

    /**
     * @return int<0, 65536>
     */
    protected function readExtWord(): int
    {
        return $this->oOutside->readWord($this->iProgramCounter);
    }

    /**
     * @return int<-32768, 32767>
     */
    protected function readExtWordSigned(): int
    {
        return Processor\Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
    }

    protected function readExtLong(): int
    {
        return $this->oOutside->readLong($this->iProgramCounter);
    }

    protected function readExtLongSigned(): int
    {
        return Processor\Sign::extLong($this->oOutside->readLong($this->iProgramCounter));
    }

}
