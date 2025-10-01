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

namespace ABadCafe\G8PHPhousand\TestHarness;

use ABadCafe\G8PHPhousand\Processor;
use ABadCafe\G8PHPhousand\Device;

use LogicException;

/**
 * Simple sparse memory wrapper
 */
class Memory
{
    public static function loadObjectCode(Device\IMemory $oMemory, ObjectCode $oObjectCode): void
    {
        $iLength  = strlen($oObjectCode->sCode);
        $iAddress = $oObjectCode->iBaseAddress;
        for ($i = 0; $i < $iLength; ++$i) {
            $oMemory->writeByte($iAddress++, Device\IByteConv::AORD[$oObjectCode->sCode[$i]]);
        }
    }

    public static function getHexDump(Device\IMemory $oMemory, int $iAddress, int $iLength): string
    {
        $sResult = '';
        while ($iLength--) {
            $sResult .= Device\IByteConv::AHEX[$oMemory->readByte($iAddress++)];
        }
        return $sResult;
    }
}
