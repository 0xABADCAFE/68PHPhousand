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

use ABadCafe\G8PHPhousand\Processor\TOpcode;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\Sign;

trait TArithmetic
{
    use TOpcode;

    use TComparisonArithmetic;
    use TBCDArithmetic;
    use TExtendedArithmetic;

    protected function initArithmeticHandlers()
    {
        $this->buildEXTHandlers();
        $this->buildCMPMHandlers();

        $aEAModes = [];
        foreach (IRegister::DATA_REGS as $iSrcReg) {
            foreach (IRegister::DATA_REGS as $iDstReg) {
                $aEAModes[] = ($iSrcReg << IOpcode::REG_UP_SHIFT) | $iDstReg;
            }
        }

        $this->buildABCDHandlers($aEAModes);
        $this->buildADDXHandlers($aEAModes);
        $this->buildSUBXHandlers($aEAModes);


        $aEAModes = $this->generateForEAModeList(
            IEffectiveAddress::MODE_DATA_ALTERABLE
        );

        $this->buildNEGHandlers($aEAModes);
        $this->buildNEGXHandlers($aEAModes);

        $this->buildADDIHandlers($aEAModes);
        $this->buildSUBIHandlers($aEAModes);

        $this->buildCMPIHandlers($aEAModes);

        $aEAAregs = $this->generateForEAModeList(
            IEffectiveAddress::MODE_ONLY_AREGS
        );

        $this->buildADDQHandlers($aEAModes, $aEAAregs);
        $this->buildSUBQHandlers($aEAModes, $aEAAregs);

        $aEAModes = $this->generateForEAModeList(
            IEffectiveAddress::MODE_ALL_EXCEPT_AREGS
        );

        $this->buildCMPHandlers($aEAModes, $aEAAregs);


        $this->buildADDEA2DHandlers($aEAModes, $aEAAregs);
        $this->buildSUBEA2DHandlers($aEAModes, $aEAAregs);
        $this->buildMULXHandlers($aEAModes);
        $this->buildDIVXHandlers($aEAModes);

        $aEAModes = $this->generateForEAModeList(
            IEffectiveAddress::MODE_MEM_ALTERABLE
        );

        $this->buildADDD2EAHandlers($aEAModes);
        $this->buildSUBD2EAHandlers($aEAModes);

        $aEAModes = $this->generateForEAModeList(
            IEffectiveAddress::MODE_ALL
        );

        $this->buildTSTHandlers($aEAModes);

        $this->buildCMPAHandlers($aEAModes);
        $this->buildADDEA2AHandlers($aEAModes);
        $this->buildSUBEA2AHandlers($aEAModes);

    }

    /**
     * EXT.bw
     *
     */
    private function buildEXTHandlers()
    {
        $oEXTTemplate = new Template\Params(
            0,
            'operation/arithmetic/ext',
            []
        );

        $aPrefixes = [
            IArithmetic::OP_EXT_W,
            IArithmetic::OP_EXT_L,
            IArithmetic::OP_EXTB_L,
        ];

        $iHandlers = [];
        foreach ($aPrefixes as $iPrefix) {
            foreach (IRegister::DATA_REGS as $iReg) {
                $oEXTTemplate->iOpcode = $iPrefix | $iReg;
                $aHandlers[$oEXTTemplate->iOpcode] = $this->compileTemplateHandler($oEXTTemplate);
            }
        }
        $this->addExactHandlers($aHandlers);
    }

    /**
     * NEG.bwl
     */
    private function buildNEGHandlers(array $aEAModes)
    {
        // NEG byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_NEG_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iDst    = $oEAMode->readByte();
                    $iRes    = -Sign::extByte($iDst) & ISize::MASK_BYTE;

                    $this->updateNZByte($iRes);
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                    $this->iConditionRegister |= $iRes ? IRegister::CCR_MASK_XC : 0;
                    $this->iConditionRegister |= ($iRes && $iRes == $iDst) ? IRegister::CCR_OVERFLOW : 0;
                    $oEAMode->writeByte($iRes);
                }
            )
        );

        // NEG word
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_NEG_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iDst    = $oEAMode->readWord();
                    $iRes    = -Sign::extWord($iDst) & ISize::MASK_WORD;

                    $this->updateNZWord($iRes);
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                    $this->iConditionRegister |= $iRes ? IRegister::CCR_MASK_XC : 0;
                    $this->iConditionRegister |= ($iRes && $iRes == $iDst) ? IRegister::CCR_OVERFLOW : 0;
                    $oEAMode->writeWord($iRes);

                }
            )
        );

        // NEG long
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_NEG_L,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iDst    = $oEAMode->readLong();
                    $iRes    = -Sign::extLong($iDst) & ISize::MASK_LONG;

                    $this->updateNZLong($iRes);
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                    $this->iConditionRegister |= $iRes ? IRegister::CCR_MASK_XC : 0;
                    $this->iConditionRegister |= ($iRes && $iRes == $iDst) ? IRegister::CCR_OVERFLOW : 0;
                    $oEAMode->writeLong($iRes);

                }
            )
        );
    }

    /**
     * MULS
     * MULU
     */
    private function buildMULXHandlers(array $aEAModes)
    {
        $cMULSHandler = function(int $iOpcode) {
            $oEAMode = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
            $iReg    = &$this->oDataRegisters->aIndex[($iOpcode >> IOpcode::REG_UP_SHIFT) & 7];
            $iValue  = Sign::extWord($iReg) * Sign::extWord($oEAMode->readWord());
            $iReg    = $iValue & ISize::MASK_LONG;
            $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
            $this->updateNZLong($iValue);
        };

        $cMULUHandler = function(int $iOpcode) {
            $oEAMode = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
            $iReg    = &$this->oDataRegisters->aIndex[($iOpcode >> IOpcode::IMM_UP_SHIFT) & 7];
            $iValue  = ($iReg & ISize::MASK_WORD) * $oEAMode->readWord();
            $iReg    = $iValue & ISize::MASK_LONG;
            $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
            $this->updateNZLong($iValue);
        };

        foreach (IRegister::DATA_REGS as $iDReg) {
            $this->addExactHandlers(
                array_fill_keys(
                    $this->mergePrefixForModeList(
                        IArithmetic::OP_MULS_W|($iDReg << IOpcode::REG_UP_SHIFT),
                        $aEAModes
                    ),
                    $cMULSHandler
                )
            );
            $this->addExactHandlers(
                array_fill_keys(
                    $this->mergePrefixForModeList(
                        IArithmetic::OP_MULU_W|($iDReg << IOpcode::REG_UP_SHIFT),
                        $aEAModes
                    ),
                    $cMULUHandler
                )
            );

        }
    }

    /**
     * DIVS
     * DIVU
     */
    private function buildDIVXHandlers(array $aEAModes)
    {
        $cDIVSHandler = function(int $iOpcode) {
            $oEAMode     = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
            $iDivisor    = Sign::extWord($oEAMode->readWord());
            $iReg        = &$this->oDataRegisters->aIndex[($iOpcode >> IOpcode::IMM_UP_SHIFT) & 7];

            $iDividend   = Sign::extLong($iReg);

            $iRemainder  = ($iDividend % $iDivisor);
            $iQuotient   = (int)(($iDividend - $iRemainder) / $iDivisor);

            if ($iQuotient < -32768 || $iQuotient > 32767) {
                $this->iConditionRegister &= IRegister::CCR_CLEAR_C;
                $this->iConditionRegister |= IRegister::CCR_OVERFLOW;
            } else {
                $iQuotient  &= ISize::MASK_WORD;
                $iRemainder &= ISize::MASK_WORD;

                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $iReg = ($iRemainder << 16) | $iQuotient;
                $this->updateNZWord($iQuotient);
            }
        };

        $cDIVUHandler = function(int $iOpcode) {
            $oEAMode     = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
            $iDivisor    = $oEAMode->readWord();
            $iReg        = &$this->oDataRegisters->aIndex[($iOpcode >> IOpcode::IMM_UP_SHIFT) & 7];

            //$iRemainder  = ($iReg % $iDivisor) & ISize::MASK_WORD;
            //$iQuotient   = (int)(($iReg - $iRemainder) / $iDivisor);

            $fResult    = $iReg / $iDivisor;
            $iQuotient  = (int)$fResult;
            $iRemainder = (int)((($fResult - $iQuotient) * $iDivisor) + 0.5);

            if ($iQuotient & ISize::MASK_INV_WORD) {
                $this->iConditionRegister &= IRegister::CCR_CLEAR_C;
                $this->iConditionRegister |= IRegister::CCR_OVERFLOW;
            } else {
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $iReg = ($iRemainder << 16) | ($iQuotient & ISize::MASK_WORD);
                $this->updateNZWord($iQuotient);
            }
        };

        foreach (IRegister::DATA_REGS as $iDReg) {
            $this->addExactHandlers(
                array_fill_keys(
                    $this->mergePrefixForModeList(
                        IArithmetic::OP_DIVS_W|($iDReg << IOpcode::REG_UP_SHIFT),
                        $aEAModes
                    ),
                    $cDIVSHandler
                )
            );
            $this->addExactHandlers(
                array_fill_keys(
                    $this->mergePrefixForModeList(
                        IArithmetic::OP_DIVU_W|($iDReg << IOpcode::REG_UP_SHIFT),
                        $aEAModes
                    ),
                    $cDIVUHandler
                )
            );
        }
    }

    /**
     * SUBI.bwl
     */
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
                    $this->iProgramCounter += ISize::WORD;
                    $iDst    = $oEAMode->readByte();
                    $iRes    = $iDst - $iSrc;
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
                    $this->iProgramCounter += ISize::WORD;
                    $iDst    = $oEAMode->readWord();
                    $iRes    = $iDst - $iSrc;
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
                    $this->iProgramCounter += ISize::LONG;
                    $iDst    = $oEAMode->readLong();
                    $iRes    = $iDst - $iSrc;
                    $this->updateCCRMathLong($iSrc, $iDst, $iRes, false);
                    $oEAMode->writeLong($iRes);
                }
            )
        );
    }

    /**
     * ADDI.bwl
     */
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
                    $this->iProgramCounter += ISize::WORD;
                    $iDst    = $oEAMode->readByte();
                    $iRes    = $iDst + $iSrc;
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
                    $this->iProgramCounter += ISize::WORD;
                    $iDst    = $oEAMode->readWord();
                    $iRes    = $iDst + $iSrc;
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
                    $this->iProgramCounter += ISize::LONG;
                    $iDst    = $oEAMode->readLong();
                    $iRes    = $iDst + $iSrc;
                    $this->updateCCRMathLong($iSrc, $iDst, $iRes, true);
                    $oEAMode->writeLong($iRes);
                }
            )
        );
    }

    /**
     * ADDQ.bwl
     */
    private function buildADDQHandlers(array $aEAModes, array $aEAAregs)
    {
        $oADDQTemplate = new Template\Params(
            0,
            'operation/arithmetic/addq',
            [
                'bAddressTarget' => false
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

        $oADDQTemplate->oAdditional->bAddressTarget = true;

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

    /**
     * SUBQ.bwl
     */
    private function buildSUBQHandlers(array $aEAModes, array $aEAAregs)
    {
        $oSUBQTemplate = new Template\Params(
            0,
            'operation/arithmetic/subq',
            [
                'bAddressTarget' => false
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

        $oSUBQTemplate->oAdditional->bAddressTarget = true;

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

        foreach (IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oADDTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oADDTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oADDTemplate)
                    )
                );
                // ADD Ax,Dy is word/long only
                if ($iPrefix !== IArithmetic::OP_ADD_EA2D_B) {
                    $oADDTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
                    $this->addExactHandlers(
                        array_fill_keys(
                            $this->mergePrefixForModeList($oADDTemplate->iOpcode, $aEAAregs),
                            $this->compileTemplateHandler($oADDTemplate)
                        )
                    );

                }
            }
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

        foreach (IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oADDTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
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

        foreach (IRegister::ADDR_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oADDTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
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

        foreach (IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oSUBTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oSUBTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oSUBTemplate)
                    )
                );
                if ($iPrefix !== IArithmetic::OP_SUB_EA2D_B) {
                    $oSUBTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
                    $this->addExactHandlers(
                        array_fill_keys(
                            $this->mergePrefixForModeList($oSUBTemplate->iOpcode, $aEAAregs),
                            $this->compileTemplateHandler($oSUBTemplate)
                        )
                    );

                }
            }
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

        foreach (IRegister::ADDR_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oSUBTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
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

        foreach (IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oSUBTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
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
