<?php

use ABadCafe\G8PHPhousand\Processor\ISize;

/**
 $iByte & ISize::SIGN_BIT_BYTE ?
            $iByte | self::EXT_BYTE :
            $iByte & ISize::MASK_BYTE
 */
function inlineSignExtByte(string $sInput): string
{
    return sprintf(
        '(%s & 0x%X ? (%s | 0x%X) : (%s & 0x%X))',
        $sInput,
        ISize::SIGN_BIT_BYTE,
        ~ISize::MASK_BYTE,
        ISize::MASK_BYTE
    );
}

function inlineSignExtWord(string $sInput): string
{
    return sprintf(
        '(%s & 0x%X ? (%s | 0x%X) : (%s & 0x%X))',
        $sInput,
        ISize::SIGN_BIT_WORD,
        ~ISize::MASK_WORD,
        ISize::MASK_WORD
    );
}

function inlineSignExtLong(string $sInput): string
{
    return sprintf(
        '(%s & 0x%X ? (%s | 0x%X) : (%s & 0x%X))',
        $sInput,
        ISize::SIGN_BIT_LONG,
        ~ISize::MASK_LONG,
        ISize::MASK_LONG
    );
}
