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

namespace ABadCafe\G8PHPhousand\Processor\Fault;

interface ISpecialStatusWord
{
    const FAULT_STAGE_C     = 0b1000000000000000;
    const FAULT_STAGE_B     = 0b0100000000000000;
    const RERUN_STAGE_C     = 0b0010000000000000;
    const RERUN_STAGE_B     = 0b0001000000000000;
    const RESERVED          = 0b0000111000001000;
    const FAULT_RERUN       = 0b0000000100000000;
    const READ_MODIFY_WRITE = 0b0000000010000000;
    const READ_OR_WRITE     = 0b0000000001000000;
    const SIZE_LONG         = 0b0000000000110000;
    const SIZE_WORD         = 0b0000000000010000;
    const FUNCTION_CODE     = 0b0000000000000111;

    // Examples

    //                          $D  $C  $7  $D
    //                          1101110001110101
    //                                       ^^^ FUNCTION_CODE: SUPER_DATA
    //                                    ^^ SIZE: TBC
    //                                   ^ READ_MODIFY_WRITE
    //                              ^^ RESERVED (undefined)
    //                             ^ RERUN_STAGE_B
    //                           ^ FAULT_STAGE_B
    //                          ^ FAULT_STAGE_C
}
