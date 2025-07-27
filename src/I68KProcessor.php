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

namespace ABadCafe\G8PHPhousand;

/**
 * Root interface for processor
 */
interface I68KProcessor extends IDevice {

    public const DATA_NAMES = [
        'd0', 'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7'
    ];

    public const ADDR_NAMES = [
        'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7'
    ];


    public function setPC(int $iAddress): self;
    public function getPC(): int;

    // These values set and get full 32-bit register contents
    public function getDataName(string $sRegName): int;
    public function setDataName(string $sRegName, int $iValue): self;

    public function getAddrName(string $sRegName): int;
    public function setAddrName(string $sRegName, int $iAddress): self;

}

