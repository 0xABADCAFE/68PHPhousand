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
        $this->buildMOVEHandlers();
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

    private function buildMOVEHandlers()
    {
        $aDstEAModes =  $this->generateForEAModeList(Processor\IEffectiveAddress::MODE_DATA_ALTERABLE);
        $aSrcEAModes = $this->generateForEAModeList(Processor\IEffectiveAddress::MODE_ALL);
        $aSizes = [
            IMove::OP_MOVE_B => function($iOpcode) {
                $iValue = $this->aDstEAModes[
                    ($iOpcode >> IMove::OP_MOVE_SRC_EA_SHIFT) & IOpcode::MASK_OP_STD_EA
                ]->readByte();
                $this->iConditionRegister &= IRegister::CCR_EXTEND;
                $this->updateNZByte($iValue);
                $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeByte($iValue);
            },
            IMove::OP_MOVE_W => function($iOpcode) {
                $iValue = $this->aDstEAModes[
                    ($iOpcode >> IMove::OP_MOVE_SRC_EA_SHIFT) & IOpcode::MASK_OP_STD_EA
                ]->readWord();
                $this->iConditionRegister &= IRegister::CCR_EXTEND;
                $this->updateNZWord($iValue);
                $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeWord($iValue);
            },
            IMove::OP_MOVE_L => function($iOpcode) {
                $iValue = $this->aDstEAModes[
                    ($iOpcode >> IMove::OP_MOVE_SRC_EA_SHIFT) & IOpcode::MASK_OP_STD_EA
                ]->readLong();
                $this->iConditionRegister &= IRegister::CCR_EXTEND;
                $this->updateNZLong($iValue);
                $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeWord($iValue);
            }
        ];

        // Size > Src EA > Dst EA
        foreach ($aSizes as $iPrefix => $cHandler) {
            foreach ($aSrcEAModes as $iSrcEAMode) {
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList(
                            $iPrefix|($iSrcEAMode << IMove::OP_MOVE_SRC_EA_SHIFT),
                            $aDstEAModes
                        ),
                        $cHandler
                    )
                );
            }
        }
    }
}
