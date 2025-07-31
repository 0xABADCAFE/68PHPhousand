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

/**
 * Effective Address Result for the Register File
 */
class RegisterFile implements IReadWrite
{
    private array $aRegisters = [];
    private int   $iRegister  = 0;

    public function __construct(array& $aRegisters)
    {
        $this->aRegisters = $aRegisters;
    }

    public function set(int $iRegIndex): void
    {
        // Bind
        $this->iRegister = &$this->aRegisters[$iRegIndex];
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

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        $this->iRegister = ($this->iRegister & 0xFFFFFF00) | ($iValue & 0xFF);
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        $this->iRegister = ($this->iRegister & 0xFFFF0000) | ($iValue & 0xFFFF);
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        $this->iRegister = $iValue & 0xFFFFFFFF;
    }
}
