./<?php

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
 * Simple Memory implementation used for diagnostic testing. Based on the SparseWordRAM,
 * includes facilities for placing object code into memory for execution.
 */
class Memory extends Device\Memory\SparseWordRAM
{
    public function loadObjectCode($oObjectCode): void
    {
        $iLength = strlen($oObjectCode->sCode);
        $aWords = array_combine(
            range(
                $oObjectCode->iBaseAddress,
                $oObjectCode->iBaseAddress + $iLength - ISize::WORD,
                ISize::WORD
            ),
            array_values(unpack('n*', $oObjectCode->sCode))
        );

        // Ensure the loaded code overwrites the expected address range
        $this->aWords = $aWords + $this->aWords;
    }

}
