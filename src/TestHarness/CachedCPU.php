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

class CachedCPU extends CPU
{
    public function __construct(Device\IBus $oOutside)
    {
        parent __construct($oOutside, true);
    }

    public function getName(): string
    {
        return 'TestHarness CPU (Cached)';
    }

    public function execute(int $iAddress): int
    {
        $this->iProgramCounter = $iAddress;
        $iCount = 0;
        try {
            $aInstCache = [];

            for(;;) {
                $iOpcode = $aInstCache[$this->iProgramCounter] ?? (
                    $aInstCache[$this->iProgramCounter] = $this->oOutside->readWord(
                        $this->iProgramCounter
                    )
                );
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;

            };
        } catch (LogicException $oError) {

        }
        return $iCount;
    }

}
