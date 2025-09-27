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

class CPU extends Processor\Base
{
    public function getName(): string
    {
        return 'TestHarness';
    }

    public function getOutside(): Device\IBus
    {
        return $this->oOutside;
    }

    public function execute(int $iAddress): int
    {
        $this->iProgramCounter = $iAddress;
        $iCount = 0;

        try {
            while(true) {
                $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                $this->iProgramCounter += Processor\ISize::WORD;
                $this->aExactHandler[$iOpcode]($iOpcode);
                ++$iCount;
            };
        } catch (LogicException $oError) {

        }
        return $iCount;
    }

}
