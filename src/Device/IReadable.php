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
 * Root interface for read accessible devices. All accesses are considered unsigned.
 */
interface IReadable {

    /**
     * @param int<0,4294967295> $iAddress
     * @return int<0,255>
     */
    public function readByte(int $iAddress): int;

    /**
     * @param int<0,4294967294> $iAddress
     * @return int<0,65535>
     */
    public function readWord(int $iAddress): int;

    /**
     * @param int<0,4294967292> $iAddress
     * @return int<0,4294967295>
     */
    public function readLong(int $iAddress): int;

}

