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

namespace ABadCafe\G8PHPhousand\Processor\EAMode\Indirect;
use ABadCafe\G8PHPhousand\Processor\EAMode\Direct;
use ABadCafe\G8PHPhousand\Device;
use ABadCafe\G8PHPhousand\Processor;

use ValueError;

/**
 * Address Register Indirect, no offsets, increment/decrement or indexing
 */
class Basic extends Direct\Register
{
    protected Device\IBus $oOutside;

    public function __construct(Processor\RegisterSet $oRegisters, Device\IBus $oOutside)
    {
        parent::__construct($oRegisters);
        $this->oOutside = $oOutside;
    }

    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        return $this->oOutside->readByte($this->iRegister);
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        return $this->oOutside->readWord($this->iRegister);
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        return $this->oOutside->readLong($this->iRegister);
    }

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        $this->oOutside->writeByte($this->iRegister, $iValue);
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        $this->oOutside->writeWord($this->iRegister, $iValue);
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $this->oOutside->writeLong($this->iRegister, $iValue);
    }
}
