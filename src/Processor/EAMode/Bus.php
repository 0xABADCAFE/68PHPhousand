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

use ValueError;

/**
 * Effective Address Result for the outside (whatever is on the bus)
 */
class Bus implements IReadWrite
{
    protected int $iAddress = 0;

    protected Device\IBus $oOutside;

    public function __construct(Device\IBus $oOutside)
    {
        $this->oOutside = $oOutside;
    }

    public function bind(int $iAddress): void
    {
        $this->iAddress = $iAddress & 0xFFFFFFFF;
    }

    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        return $this->oOutside->readByte($this->iAddress);
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        return $this->oOutside->readWord($this->iAddress);
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        return $this->oOutside->readLong($this->iAddress);
    }

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        $this->oOutside->writeByte($this->iAddress, $iValue);
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        $this->oOutside->writeWord($this->iAddress, $iValue);
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $this->oOutside->writeLong($this->iAddress, $iValue);
    }
}
