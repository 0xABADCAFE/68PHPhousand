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

    use TRegisterUnit;

    protected static function generateDisplacement(int $iAddress, int $iDisplacement): int {
        return ($iAddress + $iDisplacement) & 0xFFFFFFFF;
    }

    protected static function generateBytePostInc(int& $iAddress): int {
        $iResult = $iAddress;
        $iAddress = ($iAddress + 1) & 0xFFFFFFFF;
        return $iResult;
    }

    protected static function generateWordPostInc(int& $iAddress): int {
        $iResult = $iAddress;
        $iAddress = ($iAddress + 2) & 0xFFFFFFFF;
        return $iResult;
    }

    protected static function generateLongPostInc(int& $iAddress): int {
        $iResult = $iAddress;
        $iAddress = ($iAddress + 4) & 0xFFFFFFFF;
        return $iResult;
    }

    protected static function generateBytePreDec(int& $iAddress): int {
        $iAddress = ($iAddress - 1) & 0xFFFFFFFF;
        return $iAddress;
    }

    protected static function generateWordPreDec(int& $iAddress): int {
        $iAddress = ($iAddress - 2) & 0xFFFFFFFF;
        return $iAddress;
    }

    protected static function generateLongPreDec(int& $iAddress): int {
        $iAddress = ($iAddress - 4) & 0xFFFFFFFF;
        return $iAddress;
    }


}
