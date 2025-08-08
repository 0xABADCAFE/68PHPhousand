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

namespace ABadCafe\G8PHPhousand\Processor\EAMode\Direct;
use ABadCafe\G8PHPhousand\Processor\EAMode;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Device;

use ValueError;

/**
 * EA mode for immediates
 */
class Immediate implements EAMode\IReadOnly
{
    use EAMode\TWithBusAccess;
    use EAMode\TWithExtensionWords;

    public function __construct(int& $iProgramCounter, Device\IBus $oOutside)
    {
        $this->bindProgramCounter($iProgramCounter);
        $this->bindBus($oOutside);
    }

    public function bind(int $iIndex): void
    {

    }

    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        $iValue = $this->oOutside->readByte($this->iProgramCounter + 1);
        $this->iProgramCounter += ISize::WORD;
        return $iValue;
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        $iValue = $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;
        return $iValue;
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        $iValue = $this->oOutside->readLong($this->iProgramCounter);
        $this->iProgramCounter += ISize::LONG;
        return $iValue;
    }
}
