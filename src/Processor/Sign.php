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
 * Helper functions for converting byte/word/long values to their fully qualified
 * signed interpretation as PHP integers
 */
class Sign
{
    private const EXT_BYTE = ~ISize::MASK_BYTE;
    private const EXT_WORD = ~ISize::MASK_WORD;
    private const EXT_LONG = ~ISize::MASK_LONG;

    /**
     * @param int<0, 255>
     * @return int<-128, 127>
     */
    public static function extByte(int $iByte): int
    {
        return $iByte & ISize::SIGN_BIT_BYTE ?
            $iByte | self::EXT_BYTE :
            $iByte & ISize::MASK_BYTE;
    }

    /**
     * @param int<0, 65535>
     * @return int<-32768, 32767>
     */
    public static function extWord(int $iWord): int
    {
        return $iWord & ISize::SIGN_BIT_WORD ?
            $iWord | self::EXT_WORD :
            $iWord & ISize::MASK_WORD;
    }

    /**
     * @param int<0, 4294967295>
     * @return int<-2147483648, 2147483647>
     */
    public static function extLong(int $iLong): int
    {
        return $iLong & ISize::SIGN_BIT_LONG ?
            $iLong | self::EXT_LONG :
            $iLong & ISize::MASK_LONG;
    }
}
