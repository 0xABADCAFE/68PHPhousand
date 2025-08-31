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

namespace ABadCafe\G8PHPhousand\Processor\Opcode;

use ABadCafe\G8PHPhousand\Processor;

use LogicException;

trait TMove
{
    use Processor\TOpcode;

    protected function initMoveHandlers()
    {
        $this->buildCLRHandlers();
    }

    private function buildCLRHandlers()
    {
        $aEAModes = $this->generateForEAModeList(Processor\IEffectiveAddress::MODE_DATA_ALTERABLE);

        // Byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IMove::OP_CLR_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    // Preserve X, clear NVC and set Z
                    $this->iConditionRegister = ($this->iConditionRegister & IRegister::CCR_EXTEND)|IRegister::CCR_ZERO;
                    $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeByte(0);
                }
            )
        );

        // Word
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IMove::OP_CLR_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    // Preserve X, clear NVC and set Z
                    $this->iConditionRegister = ($this->iConditionRegister & IRegister::CCR_EXTEND)|IRegister::CCR_ZERO;
                    $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeWord(0);
                }
            )
        );

        // Long
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IMove::OP_CLR_L,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    // Preserve X, clear NVC and set Z
                    $this->iConditionRegister = ($this->iConditionRegister & IRegister::CCR_EXTEND)|IRegister::CCR_ZERO;
                    $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeWord(0);
                }
            )
        );

    }


}
