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

// LEA, PEA, Movem

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
        $this->buildEXGHandlers();
        $this->buildSCCHandlers(IMove::OP_ST,  'st');
        $this->buildSCCHandlers(IMove::OP_SF,  'sf');
        $this->buildSCCHandlers(IMove::OP_SHI, 'shi');
        $this->buildSCCHandlers(IMove::OP_SLS, 'sls');
        $this->buildSCCHandlers(IMove::OP_SCC, 'scc');
        $this->buildSCCHandlers(IMove::OP_SCS, 'scs');
        $this->buildSCCHandlers(IMove::OP_SNE, 'sne');
        $this->buildSCCHandlers(IMove::OP_SEQ, 'seq');
        $this->buildSCCHandlers(IMove::OP_SVC, 'svc');
        $this->buildSCCHandlers(IMove::OP_SVS, 'svs');
        $this->buildSCCHandlers(IMove::OP_SPL, 'spl');
        $this->buildSCCHandlers(IMove::OP_SMI, 'smi');
        $this->buildSCCHandlers(IMove::OP_SGE, 'sge');
        $this->buildSCCHandlers(IMove::OP_SLT, 'slt');
        $this->buildSCCHandlers(IMove::OP_SGT, 'sgt');
        $this->buildSCCHandlers(IMove::OP_SLE, 'sle');
        $this->buildLEAHanders();
        $this->buildPEAHanders();
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
                    $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->writeLong(0);
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
                $iValue = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readLong();
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
            ] = Sign::extByte($iOpcode & ISize::MASK_BYTE) & ISize::MASK_LONG;
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
        //$oSwapTemplate->bDumpCode = true;
        $aHandlers = [];
        foreach (IRegister::DATA_REGS as $iReg) {
            $oSwapTemplate->iOpcode = IMove::OP_SWAP | $iReg;
            $aHandlers[$oSwapTemplate->iOpcode] = $this->compileTemplateHandler($oSwapTemplate);
        }

        $this->addExactHandlers($aHandlers);
    }

    private function buildEXGHandlers()
    {
        $oEXGTemplate = new Template\Params(
            0,
            'operation/move/exg',
            [
                'iMode' => 0
            ]
        );
        //$oEXGTemplate->bDumpCode = true;
        $aHandlers = [];
        $aModes = [
            IMove::OP_EXG_DD,
            IMove::OP_EXG_AA,
            IMove::OP_EXG_DA,
        ];
        foreach ($aModes as $iMode) {
            $oEXGTemplate->oAdditional->iMode = $iMode;
            foreach (IRegister::DATA_REGS as $iXReg) {
                foreach (IRegister::DATA_REGS as $iYReg) {
                    $oEXGTemplate->iOpcode = $iMode | ($iXReg << IOpcode::REG_UP_SHIFT) | $iYReg;
                    $aHandlers[$oEXGTemplate->iOpcode] = $this->compileTemplateHandler($oEXGTemplate);
                }
            }
        }
        $this->addExactHandlers($aHandlers);
    }

    private function buildSCCHandlers(int $iPrefix, string $sName)
    {
        $oSccTemplate = new Template\Params(
            $iPrefix,
            'operation/Scc/'.$sName,
            []
        );
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ALTERABLE,
                    $iPrefix
                ),
                $this->compileTemplateHandler($oSccTemplate)
            )
        );
    }

    private function buildLEAHanders()
    {
        $oLEATemplate = new Template\Params(
            IMove::OP_LEA,
            'operation/move/lea',
            []
        );
        $aEAModes = $this->generateForEAModeList(IEffectiveAddress::MODE_CONTROL);
        foreach (IRegister::ADDR_REGS as $iReg) {
            $oLEATemplate->iOpcode = IMove::OP_LEA|($iReg << IOpcode::REG_UP_SHIFT);
            $this->addExactHandlers(
                array_fill_keys(
                    $this->mergePrefixForModeList($oLEATemplate->iOpcode, $aEAModes),
                    $this->compileTemplateHandler($oLEATemplate)
                )
            );
        }
    }

    private function buildPEAHanders()
    {
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_CONTROL,
                    IMove::OP_PEA
                ),
                function (int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->oAddressRegisters->iReg7 -= ISize::LONG;
                    $this->oAddressRegisters->iReg7 &= ISize::MASK_LONG;
                    $this->oOutside->writeLong(
                        $this->oAddressRegisters->iReg7,
                        $oEAMode->getAddress()
                    );
                }
            )
        );


    }
}
