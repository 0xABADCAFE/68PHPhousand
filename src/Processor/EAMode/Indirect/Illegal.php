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
use ABadCafe\G8PHPhousand\Processor;

use LogicException;

/**
 * Address Register Indirect, no offsets, increment/decrement or indexing
 */
class Illegal implements IIndirect
{
    private string $sError;

    public function __construct(array $aValidModes, int $iMode)
    {
        $sError = sprintf(
            'Unsupported Addressing Mode %d is not in set {%s} for ',
            $iMode,
            implode(',', $aValidModes)
        );
    }

    public function getAddress(): int
    {
        throw new LogicException($this->sError . 'getAddress()');
    }

    /**
     * @return int<0,255>
     */
    public function readByte(): int
    {
        throw new LogicException($this->sError . 'readByte()');
    }

    /**
     * @return int<0,65535>
     */
    public function readWord(): int
    {
        throw new LogicException($this->sError . 'readWord()');
    }

    /**
     * @return int<0,4294967295>
     */
    public function readLong(): int
    {
        throw new LogicException($this->sError . 'readLong()');
    }

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void
    {
        throw new LogicException($this->sError . 'writeByte()');
    }

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void
    {
        throw new LogicException($this->sError . 'writeWord()');
    }

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void
    {
        throw new LogicException($this->sError. 'writeLong()');
    }


}
