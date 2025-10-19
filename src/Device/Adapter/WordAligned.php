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

use ABadCafe\G8PHPhousand\Device\IBus;
use ABadCafe\G8PHPhousand\Processor\Fault;
use ABadCafe\G8PHPhousand\Processor\ISize;

/**
 * Alignment wrapper. Enforces word aligned access. The CPU implements handling for misaligned
 * access but never triggers it, since it's not generally an issue for the emulation, other
 * than for formal correctness with badly-behaved code.
 */
class WordAligned implements IBus
{
    private IBus $oDevice;

    protected Fault\MisalignedRead  $oMisalignedRead;
    protected Fault\MisalignedWrite $oMisalignedWrite;

    public function __construct(IBus $oDevice)
    {
        $this->oDevice = $oDevice;
        $this->oMisalignedRead  = new Fault\MisalignedRead();
        $this->oMisalignedWrite = new Fault\MisalignedWrite();
    }

    public function getName(): string
    {
        return 'Word Aligned ' . $this->oDevice->getName();
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
        return $this->oDevice->readByte($iAddress);
    }

    public function readWord(int $iAddress): int
    {
        ($iAddress & 1) && $this->oMisalignedRead->raise($iAddress, ISize::WORD);
        return $this->oDevice->readWord($iAddress);
    }

    public function readLong(int $iAddress): int
    {
        ($iAddress & 1) && $this->oMisalignedRead->raise($iAddress, ISize::LONG);
        return $this->oDevice->readLong($iAddress);
    }

    public function writeByte(int $iAddress, int $iValue): void
    {
        $this->oDevice->writeByte($iAddress, $iValue);
    }

    public function writeWord(int $iAddress, int $iValue): void
    {
        ($iAddress & 1) && $this->oMisalignedWrite->raise($iAddress, ISize::WORD);
        $this->oDevice->writeWord($iAddress, $iValue);
    }

    public function writeLong(int $iAddress, int $iValue): void
    {
        ($iAddress & 1) && $this->oMisalignedWrite->raise($iAddress, ISize::LONG);
        $this->oDevice->writeLong($iAddress, $iValue);
    }

}

