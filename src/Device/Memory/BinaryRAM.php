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

namespace ABadCafe\G8PHPhousand\Device\Memory;

use ABadCafe\G8PHPhousand\Device;

use DomainException;
use ValueError;
use function str_repeat;

/**
 * Raw Binary memory. Memory is implemented as a direct string
 */
class BinaryRAM implements Device\IMemory
{
    use Device\TAddressMapped;

    public  const MIN_ALIGNMENT = 4;

    private const ALIGN_MASK    = (self::MIN_ALIGNMENT - 1);

    private int    $iTopAddress  = 0;
    private string $sData        = '';

    public function __construct(int $iLength, int $iBaseAddress = 0)
    {
        assert(
            $iLength >= self::MIN_ALIGNMENT &&
            0 == ($iLength & self::ALIGN_MASK),
            new \ValueError('Memory length must be a positive multiple of ' . self::MIN_ALIGNMENT)
        );
        assert(
            $iBaseAddress >= 0 &&
            0 == ($iBaseAddress & self::ALIGN_MASK),
            new ValueError('Memory base address must be a positive multiple of ' . self::MIN_ALIGNMENT)
        );
        $this->iBaseAddress = $iBaseAddress;
        $this->iLength      = $iLength;
        $this->iTopAddress  = $iBaseAddress + $iLength - 1;
        $this->hardReset();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return sprintf(
            'Relocatable RAM (string, base: $%08X, length %d)',
            $this->iBaseAddress,
            $this->iLength
        );
    }

    /**
     * @inheritDoc
     */
    public function softReset(): self
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hardReset(): self
    {
        $this->sData = str_repeat("\0", $this->iLength);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readByte(int $iAddress): int
    {
        assert(
            $iAddress >= $this->iBaseAddress &&
            $iAddress <= $this->iTopAddress,
            new DomainException('Read byte out of range')
        );
        return Device\IByteConv::AORD[$this->sData[$iAddress - $this->iBaseAddress]];
    }

    /**
     * @inheritDoc
     */
    public function readWord(int $iAddress): int
    {
        $iOffset = $iAddress - $this->iBaseAddress;
        assert(
            $iOffset >= 0 &&
            $iOffset <= $this->iLength - 2,
            new DomainException('Read word out of range')
        );
        return
            Device\IByteConv::AORD[$this->sData[$iOffset]] << 8 |
            Device\IByteConv::AORD[$this->sData[$iOffset + 1]
        ];
    }

    /**
     * @inheritDoc
     */
    public function readLong(int $iAddress): int
    {
        $iOffset = $iAddress - $this->iBaseAddress;
        assert(
            $iOffset >= 0 &&
            $iOffset <= $this->iLength - 4,
            new DomainException('Read long out of range')
        );

        return
            Device\IByteConv::AORD[$this->sData[$iOffset]]     << 24 |
            Device\IByteConv::AORD[$this->sData[$iOffset + 1]] << 16 |
            Device\IByteConv::AORD[$this->sData[$iOffset + 2]] <<  8 |
            Device\IByteConv::AORD[$this->sData[$iOffset + 3]]
        ;
    }

    /**
     * @inheritDoc
     */
    public function writeByte(int $iAddress, int $iValue): void
    {
        assert(
            $iAddress >= $this->iBaseAddress &&
            $iAddress <= $this->iTopAddress,
            new DomainException('Write byte out of range')
        );
        assert(0 == ($iValue & ~0xFF), new ValueError('Illegal byte value'));
        $this->sData[$iAddress - $this->iBaseAddress] = Device\IByteConv::ACHR[$iValue];
    }

    /**
     * @inheritDoc
     */
    public function writeWord(int $iAddress, int $iValue): void
    {
        $iOffset = $iAddress - $this->iBaseAddress;
        assert(
            $iOffset >= 0 &&
            $iOffset <= $this->iLength - 2,
            new DomainException('Write word out of range')
        );
        assert(0 == ($iValue & ~0xFFFF), new ValueError('Illegal word value'));
        $this->sData[$iOffset]     = Device\IByteConv::ACHR[($iValue >> 8) & 0xFF];
        $this->sData[$iOffset + 1] = Device\IByteConv::ACHR[$iValue        & 0xFF];
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $iAddress, int $iValue): void
    {
        $iOffset = $iAddress - $this->iBaseAddress;
        assert(
            $iOffset >= 0 &&
            $iOffset <= $this->iLength - 4,
            new DomainException('Write long out of range')
        );
        assert(0 == ($iValue & ~0xFFFFFFFF), new ValueError('Illegal long value'));
        $this->sData[$iOffset] = Device\IByteConv::ACHR[($iValue >> 24)     & 0xFF];
        $this->sData[$iOffset + 1] = Device\IByteConv::ACHR[($iValue >> 16) & 0xFF];
        $this->sData[$iOffset + 2] = Device\IByteConv::ACHR[($iValue >> 8)  & 0xFF];
        $this->sData[$iOffset + 3] = Device\IByteConv::ACHR[$iValue         & 0xFF];
    }
}

