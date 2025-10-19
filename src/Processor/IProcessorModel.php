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
 * Processor model identification constants
 */
interface IProcessorModel
{
    const MC68000  = 0;
    const MC68010  = 1;
    const MC68020  = 2;
    const MC68030  = 3; // Future
    const MC68040  = 4; // Future
    const MC68060  = 5; // Future

    const NAMES = [
        self::MC68000 => 'MC68000',
        self::MC68010 => 'MC68010',
        self::MC68020 => 'MC68020',
        self::MC68030 => 'MC68030',
        self::MC68040 => 'MC68040',
        self::MC68060 => 'MC68060',
    ];

    // Address bus width by processor
    const ADDRESS_MASK = [
        self::MC68000 => 0x00FFFFFF, // 24-bit
        self::MC68010 => 0x00FFFFFF, // 24-bit
        self::MC68020 => 0xFFFFFFFF, // 32-bit
        self::MC68030 => 0xFFFFFFFF, // 32-bit
        self::MC68040 => 0xFFFFFFFF, // 32-bit
        self::MC68060 => 0xFFFFFFFF, // 32-bit
    ];
}
