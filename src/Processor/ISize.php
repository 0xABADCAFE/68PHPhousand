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
interface ISize
{
    const BYTE = 1;
    const WORD = 2;
    const LONG = 4;

    const MASK_BYTE     = 0xFF;
    const MASK_WORD     = 0xFFFF;
    const MASK_LONG     = 0xFFFFFFFF;
    const MASK_INV_BYTE = 0xFFFFFF00;
    const MASK_INV_WORD = 0xFFFF0000;

    const SIGN_BIT_BYTE = 0x80;
    const SIGN_BIT_WORD = 0x8000;
    const SIGN_BIT_LONG = 0x80000000;
}

