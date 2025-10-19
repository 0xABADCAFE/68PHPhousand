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
 * Extension Word Format Parser
 *
 * Handles both brief (68000-compatible) and full (68020+) extension word formats
 * for indexed addressing modes.
 */
class ExtensionWord
{
    // Extension word format detection
    const IS_FULL_FORMAT    = 0x0100; // Bit 8: 0=brief, 1=full

    // Brief format (68000-compatible) bits
    const BRIEF_DA          = 0x8000; // D/A bit (0=data, 1=address)
    const BRIEF_REGISTER    = 0x7000; // Register number (bits 14-12)
    const BRIEF_WL          = 0x0800; // Word/Long (0=sign-extend word, 1=long)
    const BRIEF_DISPLACEMENT = 0x00FF; // 8-bit displacement

    // Full format (68020+) bits
    const FULL_BS           = 0x0080; // Base Suppress
    const FULL_IS           = 0x0040; // Index Suppress
    const FULL_BD_SIZE      = 0x0030; // Base Displacement Size
    const FULL_IIS          = 0x0007; // Index/Indirect Selection
    const FULL_SCALE        = 0x0600; // Scale factor

    // BD Size values
    const BD_SIZE_NULL      = 0x0000; // No displacement
    const BD_SIZE_RESERVED  = 0x0010; // Reserved
    const BD_SIZE_WORD      = 0x0020; // 16-bit displacement
    const BD_SIZE_LONG      = 0x0030; // 32-bit displacement

    // I/IS values (Index/Indirect Selection)
    const IIS_NO_MEMORY_INDIRECT         = 0x0000; // No memory indirect
    const IIS_INDIRECT_PREINDEX_NULL_OD  = 0x0001; // ([bd,An,Xn*scale])
    const IIS_INDIRECT_PREINDEX_WORD_OD  = 0x0002; // ([bd,An,Xn*scale],od16)
    const IIS_INDIRECT_PREINDEX_LONG_OD  = 0x0003; // ([bd,An,Xn*scale],od32)
    const IIS_RESERVED                   = 0x0004; // Reserved
    const IIS_INDIRECT_POSTINDEX_NULL_OD = 0x0005; // ([bd,An],Xn*scale)
    const IIS_INDIRECT_POSTINDEX_WORD_OD = 0x0006; // ([bd,An],Xn*scale,od16)
    const IIS_INDIRECT_POSTINDEX_LONG_OD = 0x0007; // ([bd,An],Xn*scale,od32)

    /**
     * Check if extension word uses brief format (68000-compatible)
     */
    public static function isBriefFormat(int $iExtWord): bool
    {
        return 0 === ($iExtWord & self::IS_FULL_FORMAT);
    }

    /**
     * Get scale factor (1, 2, 4, or 8)
     * 68020+ only
     */
    public static function getScale(int $iExtWord): int
    {
        static $aScale = [1, 2, 4, 8];
        return $aScale[($iExtWord & self::FULL_SCALE) >> 9];
    }

    /**
     * Check if base register is suppressed (68020+ full format)
     */
    public static function isBaseSuppressed(int $iExtWord): bool
    {
        return 0 !== ($iExtWord & self::FULL_BS);
    }

    /**
     * Check if index register is suppressed (68020+ full format)
     */
    public static function isIndexSuppressed(int $iExtWord): bool
    {
        return 0 !== ($iExtWord & self::FULL_IS);
    }

    /**
     * Get base displacement size
     */
    public static function getBaseDisplacementSize(int $iExtWord): int
    {
        return ($iExtWord & self::FULL_BD_SIZE);
    }

    /**
     * Get index/indirect selection mode
     */
    public static function getIndexIndirectMode(int $iExtWord): int
    {
        return ($iExtWord & self::FULL_IIS);
    }

    /**
     * Check if this uses memory indirect addressing
     */
    public static function isMemoryIndirect(int $iExtWord): bool
    {
        $iIIS = self::getIndexIndirectMode($iExtWord);
        return $iIIS !== self::IIS_NO_MEMORY_INDIRECT && $iIIS !== self::IIS_RESERVED;
    }

    /**
     * Get index register number (0-7)
     */
    public static function getIndexRegister(int $iExtWord): int
    {
        return ($iExtWord & self::BRIEF_REGISTER) >> 12;
    }

    /**
     * Check if index register is address register (vs data register)
     */
    public static function isIndexAddressRegister(int $iExtWord): bool
    {
        return 0 !== ($iExtWord & self::BRIEF_DA);
    }

    /**
     * Check if index is long (vs word that needs sign extension)
     */
    public static function isIndexLong(int $iExtWord): bool
    {
        return 0 !== ($iExtWord & self::BRIEF_WL);
    }

    /**
     * Get brief format 8-bit displacement
     */
    public static function getBriefDisplacement(int $iExtWord): int
    {
        return $iExtWord & self::BRIEF_DISPLACEMENT;
    }
}
