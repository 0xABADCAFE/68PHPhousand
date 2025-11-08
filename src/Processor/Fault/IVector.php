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

interface IVector
{
    const VOFS_RESET_INITIAL_INTERRUPT_SP = 0x000;
    const VOFS_RESET_INITIAL_PC           = 0x004;
    const VOFS_ACCESS_FAULT               = 0x008;
    const VOFS_ADDRESS_ERROR              = 0x00C;
    const VOFS_ILLEGAL_INSTRUCTION        = 0x010;
    const VOFS_INTEGER_DIVIDE_BY_ZERO     = 0x014;
    const VOFS_CHK_INSTRUCTION            = 0x018;

    const VOFS_TRAPV_INSTRUCTION          = 0x01C;

    const VOFS_TRAP_USER                  = 0x080;

    const MASK_TRAP_VECTOR_NUM            = 0x00F;
}
