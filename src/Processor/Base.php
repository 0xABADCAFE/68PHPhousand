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

use ABadCafe\G8PHPhousand\I68KProcessor;

use ABadCafe\G8PHPhousand\Device;

/**
 * Base class implementation
 */
abstract class Base implements I68KProcessor {

    protected Device\IBus $oOutside;

    use TRegisterUnit;
    use TAddressUnit;

    public function __construct(Device\IBus $oOutside) {
        $this->oOutside  = $oOutside;
        $this->initRegIndexes();
        $this->softReset();
    }

    public function softReset(): self {
        $this->registerReset();
        $this->oOutside->softReset();
        return $this;
    }

    public function hardReset(): self {
        $this->registerReset();
        $this->oOutside->hardReset();
        return $this;
    }

    protected function decodeStandardIndirectEAMode(int $iOpcode): int {
        $iMode      = $iOpcode & IOpcode::MASK_EA_MODE;
        $iModeParam = $iOpcode & IOpocde::MASK_EA_REG;

        // Expecting indirect modes only.
        switch ($iMode) {
            case IOpcode::LSB_EA_AI:
                return $this->aAddrRegs[$iModeParam];

            case IOpcode::LSB_EA_AIPI:
            case IOpcode::LSB_EA_AIPD:
            case IOpcode::LSB_EA_AID:
            case IOpcode::LSB_EA_AII:
            case IOpcode::LSB_EA_D:


        }
    }

}
