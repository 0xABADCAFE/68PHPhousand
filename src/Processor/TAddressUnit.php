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

namespace ABadCafe\G8PHPhousand\Processor;

use LogicException;

/**
 * Trait for effective address calculations
 */
trait TAddressUnit {

    protected function readByteIndPostInc(int& $iAddress): int {
        $iResult = $this->oOutside->readByte($iAddress);
        $iAddress = ($iAddress + 1) & 0xFFFFFFFF;
        return $iResult;
    }

    protected function readWordIndPostInc(int& $iAddress): int {
        assert(0 == ($iAddress & 1), new LogicException('Misaligned word access'));
        $iResult = $this->oOutside->readWord($iAddress);
        $iAddress = ($iAddress + 2) & 0xFFFFFFFF;
        return $iResult;
    }

    protected function readLongIndPostInc(int& $iAddress): int {
        assert(0 == ($iAddress & 1), new LogicException('Misaligned long access'));
        $iResult = $this->oOutside->readLong($iAddress);
        $iAddress = ($iAddress + 4) & 0xFFFFFFFF;
        return $iResult;
    }

    protected function readByteIndPreDec(int& $iAddress): int {
        $iAddress = ($iAddress - 1) & 0xFFFFFFFF;
        return $this->oOutside->readByte($iAddress);
    }

    protected function readWordIndPreDec(int& $iAddress): int {
        assert(0 == ($iAddress & 1), new LogicException('Misaligned word access'));
        $iAddress = ($iAddress - 2) & 0xFFFFFFFF;
        return $this->oOutside->readWord($iAddress);
    }

    protected function readLongIndPreDec(int& $iAddress): int {
        assert(0 == ($iAddress & 1), new LogicException('Misaligned long access'));
        $iAddress = ($iAddress - 4) & 0xFFFFFFFF;
        return $this->oOutside->readLong($iAddress);
    }

}
