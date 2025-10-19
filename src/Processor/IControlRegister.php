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
 * Control Register constants for MOVEC instruction
 *
 * These are used by the 68010+ MOVEC instruction to access special control registers.
 * Format: MOVEC Rc,Rn or MOVEC Rn,Rc where Rc is a control register and Rn is a general register.
 */
interface IControlRegister
{
    // Control register codes (12-bit values)
    const SFC  = 0x000;  // Source Function Code (68010+)
    const DFC  = 0x001;  // Destination Function Code (68010+)
    const CACR = 0x002;  // Cache Control Register (68020+)
    const USP  = 0x800;  // User Stack Pointer (68010+)
    const VBR  = 0x801;  // Vector Base Register (68010+)
    const CAAR = 0x802;  // Cache Address Register (68020+)
    const MSP  = 0x803;  // Master Stack Pointer (68020+)
    const ISP  = 0x804;  // Interrupt Stack Pointer (68020+)

    // Function code values (3 bits) for SFC/DFC and MOVES
    const FC_USER_DATA       = 1;
    const FC_USER_PROGRAM    = 2;
    const FC_SUPERVISOR_DATA = 5;
    const FC_SUPERVISOR_PROGRAM = 6;
    const FC_CPU_SPACE       = 7;

    // CACR bits (68020)
    const CACR_ENABLE        = 0x0001; // Enable instruction cache
    const CACR_FREEZE        = 0x0002; // Freeze instruction cache
    const CACR_CLEAR_ENTRY   = 0x0004; // Clear cache entry (write-only)
    const CACR_CLEAR_ALL     = 0x0008; // Clear entire cache (write-only)

    // Control register names for debugging
    const NAMES = [
        self::SFC  => 'SFC',
        self::DFC  => 'DFC',
        self::CACR => 'CACR',
        self::USP  => 'USP',
        self::VBR  => 'VBR',
        self::CAAR => 'CAAR',
        self::MSP  => 'MSP',
        self::ISP  => 'ISP',
    ];

    // Processor model requirements for each control register
    const MIN_MODEL = [
        self::SFC  => IProcessorModel::MC68010,
        self::DFC  => IProcessorModel::MC68010,
        self::CACR => IProcessorModel::MC68020,
        self::USP  => IProcessorModel::MC68010,
        self::VBR  => IProcessorModel::MC68010,
        self::CAAR => IProcessorModel::MC68020,
        self::MSP  => IProcessorModel::MC68020,
        self::ISP  => IProcessorModel::MC68020,
    ];
}
