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

/**
 * Root interface for write accessible devices. All accesses are considered unsigned.
 */
interface IWriteable
{
    /**
     * @param int<0,4294967295> $iAddress
     * @param int<0,255>        $iValue
     */
    public function writeByte(int $iAddress, int $iValue): void;

    /**
     * @param int<0,4294967294> $iAddress
     * @param int<0,65535>      $iValue
     */
    public function writeWord(int $iAddress, int $iValue): void;

    /**
     * @param int<0,4294967292> $iAddress
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iAddress, int $iValue): void;

}

