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

namespace ABadCafe\G8PHPhousand\Processor\EATarget;

use ABadCafe\G8PHPhousand\Processor;

use ValueError;

/**
 * Effective Address Result for the Register File
 */
abstract class Register implements IReadWrite
{
    protected int $iRegister = 0;

    private array $aRegisterIndex;

    public function __construct(Processor\RegisterSet $oRegisters)
    {
        $this->aRegisterIndex = &$oRegisters->aIndex;
    }

    public function bind(int $iIndex): void
    {
        assert(
            isset($this->aRegisterIndex[$iIndex]),
            new ValueError()
        );

        // Bind
        $this->iRegister = &$this->aRegisterIndex[$iIndex];
    }

    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        return $this->iRegister & 0xFF;
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        return $this->iRegister & 0xFFFF;
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        return $this->iRegister & 0xFFFFFFFF;
    }
}
