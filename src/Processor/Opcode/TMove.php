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

use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\Sign;
use LogicException;

trait TMove
{
    use Processor\TOpcode;

    private $aMoveDstEAModes = [];

    /** @var array<int, int> */
    private $aEAToDstEAMap = [];

    protected function initMoveHandlers()
    {
        $this->initMoveDstEAModes();
        $this->buildCLRHandlers();
        $this->buildMOVEHandlers();
        $this->buildMOVEAHandlers();
        $this->buildMOVEQHandlers();
        $this->buildSWAPHandlers();
    }

    protected function initMoveDstEAModes()
    {
        for ($i = 0; $i < 64; ++$i) {
            $this->aEAToDstEAMap[$i] = (($i >> 3) | (($i & 7) << 3)) << IMove::OP_MOVE_SRC_EA_SHIFT;
        }

        foreach ($this->aDstEAModes as $iMode => $oEAMode) {
            $this->aMoveDstEAModes[$this->aEAToDstEAMap[$iMode]] = $oEAMode;
        }
    }

    private function buildCLRHandlers()
    {
        $aEAModes = $this->generateForEAModeList(IEffectiveAddress::MODE_DATA_ALTERABLE);

        // Byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IMove::OP_CLR_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    // Preserve X, clear NVC and set Z
                    $this->iConditionRegister = (
                        $this->iConditionRegister & IRegister::CCR_EXTEND
                    ) | IRegister::CCR_ZERO;
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
                    $this->iConditionRegister = (
                        $this->iConditionRegister & IRegister::CCR_EXTEND
                    ) | IRegister::CCR_ZERO;
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
                    $this->iConditionRegister = (
                        $this->iConditionRegister & IRegister::CCR_EXTEND
                    ) | IRegister::CCR_ZERO;
                    $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeWord(0);
                }
            )
        );
    }

    private function buildMOVEHandlers()
    {
        $aDstEAModes = $this->generateForEAModeList(IEffectiveAddress::MODE_DATA_ALTERABLE);
        $aSrcEAModes = $this->generateForEAModeList(IEffectiveAddress::MODE_ALL);
        $aSizes = [
            IMove::OP_MOVE_B => function($iOpcode) {
                $iValue = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readByte();
                $this->iConditionRegister &= IRegister::CCR_EXTEND;
                $this->updateNZByte($iValue);
                $this->aMoveDstEAModes[$iOpcode & IMove::MASK_DST_EA]->writeByte($iValue);
            },
            IMove::OP_MOVE_W => function($iOpcode) {
                $iValue = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readWord();
                $this->iConditionRegister &= IRegister::CCR_EXTEND;
                $this->updateNZWord($iValue);
                $this->aMoveDstEAModes[$iOpcode & IMove::MASK_DST_EA]->writeWord($iValue);
            },
            IMove::OP_MOVE_L => function($iOpcode) {
                $iValue = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readLong();
                $this->iConditionRegister &= IRegister::CCR_EXTEND;
                $this->updateNZLong($iValue);
                $this->aMoveDstEAModes[$iOpcode & IMove::MASK_DST_EA]->writeLong($iValue);
            }
        ];

        // Size > Dst EA > Src EA
        foreach ($aSizes as $iPrefix => $cHandler) {
            foreach ($aDstEAModes as $iDstEAMode) {
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList(
                            $iPrefix | ($this->aEAToDstEAMap[$iDstEAMode]),
                            $aSrcEAModes
                        ),
                        $cHandler
                    )
                );
            }
        }
    }

    private function buildMOVEAHandlers()
    {
        $aSrcEAModes = $this->generateForEAModeList(IEffectiveAddress::MODE_ALL);
        $aSizes = [
            IMove::OP_MOVE_W|IMove::OP_MOVEA => function($iOpcode) {
                $this->oAddressRegisters->aIndex[
                    ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
                ] = Sign::extWord(
                    $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readWord()
                );
            },
            IMove::OP_MOVE_L|IMove::OP_MOVEA => function($iOpcode) {
                $this->oAddressRegisters->aIndex[
                    ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
                ] = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readLong();
            }
        ];

        foreach ($aSizes as $iPrefix => $cHandler) {
            foreach (IRegister::ADDR_REGS as $iAddrReg) {
                $iOpcode = $iPrefix | ($iAddrReg << IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList(
                            $iOpcode,
                            $aSrcEAModes
                        ),
                        $cHandler
                    )
                );
            }
        }
    }

    private function buildMOVEQHandlers()
    {
        // LSB is immediate -128 to 127
        $cZeroHandler = function(int $iOpcode) {
            $this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ] = 0;
            $this->iConditionRegister = (
                $this->iConditionRegister & IRegister::CCR_EXTEND
            ) | IRegister::CCR_ZERO;
        };
        $cPosHandler = function(int $iOpcode) {
            $this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ] = $iOpcode & ISize::MASK_BYTE;
            $this->iConditionRegister &= IRegister::CCR_EXTEND;
        };
        $cNegHandler = function(int $iOpcode) {
            $this->oDataRegisters->aIndex[
                ($iOpcode & IOpcode::MASK_REG_UPPER) >> IOpcode::REG_UP_SHIFT
            ] = Sign::extByte($iOpcode & ISize::MASK_BYTE);
            $this->iConditionRegister = (
                $this->iConditionRegister & IRegister::CCR_EXTEND
            ) | IRegister::CCR_NEGATIVE;
        };

        foreach (IRegister::DATA_REGS as $iDataReg) {
            $iPrefix = IMove::OP_MOVEQ | ($iDataReg << IOpcode::REG_UP_SHIFT);
            $this->addExactHandlers([
                $iPrefix => $cZeroHandler
            ]);
            $this->addExactHandlers(
                array_fill_keys(
                    range($iPrefix + 0x1, $iPrefix + 0x7F),
                    $cPosHandler
                )
            );
            $this->addExactHandlers(
                array_fill_keys(
                    range($iPrefix + 0x80, $iPrefix + 0xFF),
                    $cNegHandler
                )
            );

        }
    }

    private function buildSWAPHandlers()
    {
        $oSwapTemplate = new Template\Params(
            0,
            'operation/move/swap',
            []
        );
        $oSwapTemplate->bDumpCode = true;
        $aHandlers = [];
        foreach (IRegister::DATA_REGS as $iReg) {
            $oSwapTemplate->iOpcode = IMove::OP_SWAP | $iReg;
            $aHandlers[$oSwapTemplate->iOpcode] = $this->compileTemplateHandler($oSwapTemplate);
        }

        $this->addExactHandlers($aHandlers);
    }
}
