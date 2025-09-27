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

trait TArithmetic
{
    use Processor\TOpcode;

    protected function initArithmeticHandlers()
    {
        $aEAModes = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_DATA_ALTERABLE
        );

        $this->buildADDIHandlers($aEAModes);
        $this->buildSUBIHandlers($aEAModes);

        $aEAAregs = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_ONLY_AREGS
        );

        $this->buildADDQHandlers($aEAModes, $aEAAregs);
        $this->buildSUBQHandlers($aEAModes, $aEAAregs);

        $aEAModes = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_ALL_EXCEPT_AREGS
        );

        $this->buildADDEA2DHandlers($aEAModes, $aEAAregs);
        $this->buildSUBEA2DHandlers($aEAModes, $aEAAregs);
        $this->buildMULXHandlers($aEAModes);

        $aEAModes = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_MEM_ALTERABLE
        );

        $this->buildADDD2EAHandlers($aEAModes);
        $this->buildSUBD2EAHandlers($aEAModes);

        $aEAModes = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_ALL
        );
        $this->buildADDEA2AHandlers($aEAModes);
        $this->buildSUBEA2AHandlers($aEAModes);
    }

    private function buildMULXHandlers(array $aEAModes)
    {
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_MULS_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iReg    = &$this->oDataRegisters->aIndex[($iOpcode >> IOpcode::IMM_UP_SHIFT) & 7];
                    $iValue  = Sign::extWord($iReg) * Sign::extWord($oEAMode->readWord());
                    $iReg    = $iValue & ISize::MASK_LONG;
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZLong($iValue);
                }
            )
        );
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_MULU_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iReg    = &$this->oDataRegisters->aIndex[($iOpcode >> IOpcode::IMM_UP_SHIFT) & 7];
                    $iValue  = $iReg * $oEAMode->readWord();
                    $iReg    = $iValue & ISize::MASK_LONG;
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZLong($iValue);
                }
            )
        );

    }

    private function buildSUBIHandlers(array $aEAModes)
    {
        // SUBI byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_SUBI_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $iDst    = $oEAMode->readByte();
                    $iRes    = $iDst - $iSrc;
                    $this->iProgramCounter += ISize::WORD;
                    $this->updateCCRMathByte($iSrc, $iDst, $iRes, false);
                    $oEAMode->writeByte($iRes);
                }
            )
        );

        // SUBI word
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_SUBI_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readWord($this->iProgramCounter);
                    $iDst    = $oEAMode->readWord();
                    $iRes    = $iDst - $iSrc;
                    $this->iProgramCounter += ISize::WORD;
                    $this->updateCCRMathWord($iSrc, $iDst, $iRes, false);
                    $oEAMode->writeWord($iRes);
                }
            )
        );

        // SUBI long
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_SUBI_L,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readLong($this->iProgramCounter);
                    $iDst    = $oEAMode->readLong();
                    $iRes    = $iDst - $iSrc;
                    $this->iProgramCounter += ISize::LONG;
                    $this->updateCCRMathLong($iSrc, $iDst, $iRes, false);
                    $oEAMode->writeLong($iRes);
                }
            )
        );
    }

    private function buildADDIHandlers(array $aEAModes)
    {
        // ADDI byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_ADDI_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $iDst    = $oEAMode->readByte();
                    $iRes    = $iDst + $iSrc;
                    $this->iProgramCounter += ISize::WORD;
                    $this->updateCCRMathByte($iSrc, $iDst, $iRes, true);
                    $oEAMode->writeByte($iRes);
                }
            )
        );

        // ADDI word
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_ADDI_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readWord($this->iProgramCounter);
                    $iDst    = $oEAMode->readWord();
                    $iRes    = $iDst + $iSrc;
                    $this->iProgramCounter += ISize::WORD;
                    $this->updateCCRMathWord($iSrc, $iDst, $iRes, true);
                    $oEAMode->writeWord($iRes);
                }
            )
        );

        // ADDI long
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_ADDI_L,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readLong($this->iProgramCounter);
                    $iDst    = $oEAMode->readLong();
                    $iRes    = $iDst + $iSrc;
                    $this->iProgramCounter += ISize::LONG;
                    $this->updateCCRMathLong($iSrc, $iDst, $iRes, true);
                    $oEAMode->writeLong($iRes);
                }
            )
        );
    }

    private function buildADDQHandlers(array $aEAModes, array $aEAAregs)
    {
        $oADDQTemplate = new Template\Params(
            0,
            'operation/arithmetic/addq',
            [
                'bNoCCR' => false
            ]
        );

        $aPrefixes = [
            IArithmetic::OP_ADDQ_B,
            IArithmetic::OP_ADDQ_W,
            IArithmetic::OP_ADDQ_L,
        ];
        foreach ($aPrefixes as $iPrefix) {
            for ($iImmediate = 0; $iImmediate < 8; ++$iImmediate) {
                $oADDQTemplate->iOpcode = $iPrefix | ($iImmediate << 9);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oADDQTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oADDQTemplate)
                    )
                );
            }
        }

        $oADDQTemplate->oAdditional->bNoCCR = true;

        // Address registers are a special case
        $aPrefixes = [
            IArithmetic::OP_ADDQ_W,
            IArithmetic::OP_ADDQ_L,
        ];
        foreach ($aPrefixes as $iPrefix) {
            for ($iImmediate = 0; $iImmediate < 8; ++$iImmediate) {
                $oADDQTemplate->iOpcode = $iPrefix | ($iImmediate << 9);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oADDQTemplate->iOpcode, $aEAAregs),
                        $this->compileTemplateHandler($oADDQTemplate)
                    )
                );
            }
        }

    }

    private function buildSUBQHandlers(array $aEAModes, array $aEAAregs)
    {
        $oSUBQTemplate = new Template\Params(
            0,
            'operation/arithmetic/subq',
            [
                'bNoCCR' => false
            ]
        );

        $aPrefixes = [
            IArithmetic::OP_SUBQ_B,
            IArithmetic::OP_SUBQ_W,
            IArithmetic::OP_SUBQ_L,
        ];
        foreach ($aPrefixes as $iPrefix) {
            for ($iImmediate = 0; $iImmediate < 8; ++$iImmediate) {
                $oSUBQTemplate->iOpcode = $iPrefix | ($iImmediate << 9);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oSUBQTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oSUBQTemplate)
                    )
                );
            }
        }

        $oSUBQTemplate->oAdditional->bNoCCR = true;

        // Address registers are a special case
        $aPrefixes = [
            IArithmetic::OP_SUBQ_W,
            IArithmetic::OP_SUBQ_L,
        ];
        foreach ($aPrefixes as $iPrefix) {
            for ($iImmediate = 0; $iImmediate < 8; ++$iImmediate) {
                $oSUBQTemplate->iOpcode = $iPrefix | ($iImmediate << 9);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oSUBQTemplate->iOpcode, $aEAAregs),
                        $this->compileTemplateHandler($oSUBQTemplate)
                    )
                );
            }
        }

    }

    private function buildADDEA2DHandlers(array $aEAModes, array $aEAAregs)
    {
        $oADDTemplate = new Template\Params(
            0,
            'operation/arithmetic/add_ea2d',
            []
        );

        $aPrefixes = [
            IArithmetic::OP_ADD_EA2D_B,
            IArithmetic::OP_ADD_EA2D_W,
            IArithmetic::OP_ADD_EA2D_L,
        ];

        foreach (Processor\IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oADDTemplate->iOpcode = $iPrefix | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oADDTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oADDTemplate)
                    )
                );
            }

            // Address reg EA support word size only
            $oADDTemplate->iOpcode = IArithmetic::OP_ADD_EA2D_W | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
            $this->addExactHandlers(
                array_fill_keys(
                    $this->mergePrefixForModeList($oADDTemplate->iOpcode, $aEAAregs),
                    $this->compileTemplateHandler($oADDTemplate)
                )
            );
        }
    }

    private function buildADDD2EAHandlers(array $aEAModes)
    {
        $oADDTemplate = new Template\Params(
            0,
            'operation/arithmetic/add_d2ea',
            []
        );

        $aPrefixes = [
            IArithmetic::OP_ADD_D2EA_B,
            IArithmetic::OP_ADD_D2EA_W,
            IArithmetic::OP_ADD_D2EA_L,
        ];

        foreach (Processor\IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oADDTemplate->iOpcode = $iPrefix | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oADDTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oADDTemplate)
                    )
                );
            }
        }
    }

    private function buildADDEA2AHandlers(array $aEAModes)
    {
        $oADDTemplate = new Template\Params(
            0,
            'operation/arithmetic/add_ea2a',
            []
        );

        $aPrefixes = [
            IArithmetic::OP_ADD_EA2A_W,
            IArithmetic::OP_ADD_EA2A_L,
        ];

        foreach (Processor\IRegister::ADDR_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oADDTemplate->iOpcode = $iPrefix | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oADDTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oADDTemplate)
                    )
                );
            }
        }
    }

    private function buildSUBEA2DHandlers(array $aEAModes, array $aEAAregs)
    {
        $oSUBTemplate = new Template\Params(
            0,
            'operation/arithmetic/sub_ea2d',
            []
        );

        $aPrefixes = [
            IArithmetic::OP_SUB_EA2D_B,
            IArithmetic::OP_SUB_EA2D_W,
            IArithmetic::OP_SUB_EA2D_L,
        ];

        foreach (Processor\IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oSUBTemplate->iOpcode = $iPrefix | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oSUBTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oSUBTemplate)
                    )
                );
            }

            // Address reg EA support word size only
            $oSUBTemplate->iOpcode = IArithmetic::OP_SUB_EA2D_W | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
            $this->addExactHandlers(
                array_fill_keys(
                    $this->mergePrefixForModeList($oSUBTemplate->iOpcode, $aEAAregs),
                    $this->compileTemplateHandler($oSUBTemplate)
                )
            );
        }
    }

    private function buildSUBEA2AHandlers(array $aEAModes)
    {
        $oSUBTemplate = new Template\Params(
            0,
            'operation/arithmetic/sub_ea2a',
            []
        );

        $aPrefixes = [
            IArithmetic::OP_SUB_EA2A_W,
            IArithmetic::OP_SUB_EA2A_L,
        ];

        foreach (Processor\IRegister::ADDR_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oSUBTemplate->iOpcode = $iPrefix | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oSUBTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oSUBTemplate)
                    )
                );
            }
        }
    }

    private function buildSUBD2EAHandlers(array $aEAModes)
    {
        $oSUBTemplate = new Template\Params(
            0,
            'operation/arithmetic/sub_d2ea',
            []
        );

        $aPrefixes = [
            IArithmetic::OP_SUB_D2EA_B,
            IArithmetic::OP_SUB_D2EA_W,
            IArithmetic::OP_SUB_D2EA_L,
        ];

        foreach (Processor\IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oSUBTemplate->iOpcode = $iPrefix | ($iReg << Processor\IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oSUBTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oSUBTemplate)
                    )
                );
            }
        }
    }


}
