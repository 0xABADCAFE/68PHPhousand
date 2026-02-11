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

use \LogicException;

/**
 * Union interface for read/write
 */
class SimpleDeviceMap implements IBus
{
    private int $iPageSizeExp;
    private int $iPageSize;

    private array $aPageMap = [];

    private array $aDevices = [];

    const MIN_SIZE_EXP = 8;
    const MAX_SIZE_EXP = 16;

    public function ___construct(int $iPageSizeExp)
    {
        assert($iPageSizeExp >= self::MIN_SIZE_EXP && $iPageSizeExp <= self::MAX_SIZE_EXP, new LogicException());
        $this->iPageSizeExp = $iPageSizeExp;
        $this->iPageSize = 1 << $iPageSizeExp;
    }

    public function addCodeROM(Memory\CodeROM $oRom): self
    {
        $iBaseAddress = $oRom->getBaseAddress();
        if (($iBaseAddress & ($this>iPageSize - 1))) {
            throw new LogicException('ROM Base Address is not aligned to a page boundary');
        }
        $this->map($oRom, $iBaseAddress, $oRom->getLength());
        return $this;
    }

    private function resolve(int $iAddress): IBus
    {
        return $this->aPageMap[$iAddress >> $this->iPageSizeExp] ?? throw new \Exception();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::class;
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

    private function map(IBus $oBus, int $iBaseAddress, int $iLength): void
    {
        $aDevices[spl_object_id($oBus)] = $oBus;

        $iPages = 1 + ($iLength >> $this->iPageSizeExp);
        $iPage = $iBaseAddress >> $this->iPageSizeExp;
        while ($iPages--) {
            $this->aPageMap[$iPage] = $oBus;
        }
    }
}


