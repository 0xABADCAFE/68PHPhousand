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

/**
 * Basic register enumerations
 */
interface IRegister {

    public const DATA_NAMES = [
        'd0', 'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7'
    ];

    public const ADDR_NAMES = [
        'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7'
    ];

    public const X0 = 0;
    public const X1 = 1;
    public const X2 = 2;
    public const X3 = 3;
    public const X4 = 4;
    public const X5 = 5;
    public const X6 = 6;
    public const X7 = 7;

    public const A0 = self::X0;
    public const A1 = self::X1;
    public const A2 = self::X2;
    public const A3 = self::X3;
    public const A4 = self::X4;
    public const A5 = self::X5;
    public const A6 = self::X6;
    public const A7 = self::X7;
    public const SP = self::X7;

    public const D0 = self::X0;
    public const D1 = self::X1;
    public const D2 = self::X2;
    public const D3 = self::X3;
    public const D4 = self::X4;
    public const D5 = self::X5;
    public const D6 = self::X6;
    public const D7 = self::X7;
}

