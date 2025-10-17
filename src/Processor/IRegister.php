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
interface IRegister
{
    // General enumerations
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

    // CCR Bits (lower byte of SR)
    //                                XNZVC
    public const CCR_CARRY     = 0b00000001;
    public const CCR_OVERFLOW  = 0b00000010;
    public const CCR_ZERO      = 0b00000100;
    public const CCR_NEGATIVE  = 0b00001000;
    public const CCR_EXTEND    = 0b00010000;
    public const CCR_MASK      = 0b00011111;
    public const CCR_MASK_NV   = 0b00001010;
    public const CCR_MASK_ZNV  = 0b00001110;
    public const CCR_MASK_ZC   = 0b00000101;
    public const CCR_MASK_NZVC = 0b00001111;
    public const CCR_MASK_XC   = 0b00010001;

    //                                XNZVC
    public const CCR_CLEAR_N   = 0b00010111;
    public const CCR_CLEAR_Z   = 0b00011011;
    public const CCR_CLEAR_V   = 0b00011101;
    public const CCR_CLEAR_C   = 0b00011110;
    public const CCR_CLEAR_X   = 0b00001111;
    public const CCR_CLEAR_NZ  = 0b00010011;
    public const CCR_CLEAR_CV  = 0b00011100;
    public const CCR_CLEAR_XCV = 0b00001100;
    public const CCR_CLEAR_XC  = 0b00001110;


    // SR Bits (upper byte of SR)
    public const SR_MASK_INT_MASK = 0b00000111;
    public const SR_MASK_SUPER    = 0b00100000;
    public const SR_MASK_TRACE    = 0b10000000;
    public const SR_MASK          = 0b10100111;

    public const SR_CCR_MASK      = self::SR_MASK << 8 | self::CCR_MASK;


    public const DATA_REGS = [
        self::D0, self::D1, self::D2, self::D3, self::D4, self::D5, self::D6, self::D7
    ];

    public const ADDR_REGS = [
        self::A0, self::A1, self::A2, self::A3, self::A4, self::A5, self::A6, self::A7
    ];


    // Names
    public const DATA_NAMES = [
        'd0', 'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7'
    ];

    public const ADDR_NAMES = [
        'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6', 'a7'
    ];

    public const PC_NAME  = 'pc';
    public const SR_NAME  = 'sr';
    public const CCR_NAME = 'ccr';
    public const USP_NAME = 'usp';
    public const SSP_NAME = 'ssp';
    public const VBR_NAME = 'vbr'; // 010+
}

