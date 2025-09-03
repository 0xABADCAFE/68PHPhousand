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
use ABadCafe\G8PHPhousand\Processor\ISize;
use LengthException;
use DomainException;

/**
 * CodeROM
 *
 * Manages a read only set of data. Optimised for word access, data are assumed to be code.
 */
class CodeROM implements IBus
{
    private array $aWords = [];

    public function __construct(string $sFile, int $iBaseAddress = 0)
    {
        assert(!empty($sFile), new DomainException('No ROM file specified'));
        assert(0 === ($iBaseAddress & 1), new LogicException('Misaligned ROM Base Address'));
        $sData = file_get_contents($sFile);
        if (empty($sData)) {
            throw new LengthException('Empty ROM file');
        }
        // Make sure the data is an even length

        $iLength = strlen($sData);

        if ($iLength & 1) {
            ++$iLength;
            $sData .= "\0";
        }
        $this->aWords = array_combine(
            range($iBaseAddress, $iBaseAddress + $iLength - ISize::WORD, ISize::WORD),
            array_values(unpack('n*', $sData))
        );
    }

    public function getBaseAddress(): int
    {
        return $this->iBaseAddress;
    }

    public function getLength(): int
    {
        return $this->iLength;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'CodeROM';
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
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readByte(int $iAddress): int
    {
        $iWord = $this->readWord($iAddress);
        return ($iAddress & 1) ? ($iWord & ISize::MASK_BYTE) : (($iWord >> 8) & $iMaskByte);
    }

    /**
     * @inheritDoc
     */
    public function readWord(int $iAddress): int
    {
        return $this->aWords[$iAddress & 0xFFFFFFFE] ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function readLong(int $iAddress): int
    {
        return ($this->readWord($iAddress) << 16) | $this->readWord($iAddress + ISize::WORD);
    }

    /**
     * @inheritDoc
     */
    public function writeByte(int $iAddress, int $iValue): void
    {
    }

    /**
     * @inheritDoc
     */
    public function writeWord(int $iAddress, int $iValue): void
    {
    }

    /**
     * @inheritDoc
     */
    public function writeLong(int $iAddress, int $iValue): void
    {
    }

    public function getDump($iAddress, $iLength): string
    {
        return '';
    }
}
