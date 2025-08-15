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
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor;

use ValueError;

/**
 * Common Base class for register direct modes, handles binding to a register set.
 */
abstract class Register implements Processor\EAMode\IReadWrite
{
    protected int $iRegister = 0;

    public function __construct(Processor\RegisterSet $oRegisters, int $iBaseReg)
    {
        $this->iRegister = &$oRegisters->aIndex[$iBaseReg];
    }

    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        return $this->iRegister & ISize::MASK_BYTE;
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        return $this->iRegister & ISize::MASK_WORD;
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        return $this->iRegister & ISize::MASK_LONG;
    }

}
