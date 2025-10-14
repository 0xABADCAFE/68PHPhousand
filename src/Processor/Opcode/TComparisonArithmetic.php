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

trait TComparisonArithmetic
{
    /**
     * TST.bwl
     *
     */
    private function buildTSTHandlers(array $aEAModes)
    {
        // TST byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_TST_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZByte(
                        $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readByte()
                    );
                }
            )
        );

        // TST word
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_TST_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZWord(
                        $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readWord()
                    );
                }
            )
        );

        // TST long
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_TST_L,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $this->updateNZLong(
                        $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->readLong()
                    );
                }
            )
        );
    }

    /**
     * CMP.bwl
     *
     */
    private function buildCMPHandlers(array $aEAModes, array $aEAAregs)
    {
        $oCMPTemplate = new Template\Params(
            0,
            'operation/arithmetic/cmp',
            []
        );

        //$oCMPTemplate->bDumpCode = true;

        $aPrefixes = [
            IArithmetic::OP_CMP_B,
            IArithmetic::OP_CMP_W,
            IArithmetic::OP_CMP_L,
        ];

        foreach (IRegister::DATA_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oCMPTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oCMPTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oCMPTemplate)
                    )
                );
                // word and long mode only where <ea> is an Address Register
                if ($iPrefix !== IArithmetic::OP_CMP_B) {
                    $this->addExactHandlers(
                        array_fill_keys(
                            $this->mergePrefixForModeList($oCMPTemplate->iOpcode, $aEAAregs),
                            $this->compileTemplateHandler($oCMPTemplate)
                        )
                    );
                }
            }
        }
    }

    /**
     * CMPA.bwl
     *
     */
    private function buildCMPAHandlers(array $aEAModes)
    {
        $oCMPTemplate = new Template\Params(
            0,
            'operation/arithmetic/cmpa',
            []
        );

        //$oCMPTemplate->bDumpCode = true;

        $aPrefixes = [
            IArithmetic::OP_CMPA_W,
            IArithmetic::OP_CMPA_L,
        ];

        foreach (IRegister::ADDR_REGS as $iReg) {
            foreach ($aPrefixes as $iPrefix) {
                $oCMPTemplate->iOpcode = $iPrefix | ($iReg << IOpcode::REG_UP_SHIFT);
                $this->addExactHandlers(
                    array_fill_keys(
                        $this->mergePrefixForModeList($oCMPTemplate->iOpcode, $aEAModes),
                        $this->compileTemplateHandler($oCMPTemplate)
                    )
                );
            }
        }
    }


    /**
     * CMPM.bwl
     *
     */
    private function buildCMPMHandlers()
    {
        $oCMPTemplate = new Template\Params(
            0,
            'operation/arithmetic/cmpm',
            []
        );
        $aPrefixes = [
            IArithmetic::OP_CMPM_B,
            IArithmetic::OP_CMPM_W,
            IArithmetic::OP_CMPM_L,
        ];
        $aHandlers = [];
        foreach (IRegister::ADDR_REGS as $iXReg) {
            foreach ($aPrefixes as $iPrefix) {
                foreach (IRegister::ADDR_REGS as $iYReg) {
                    $oCMPTemplate->iOpcode = $iPrefix | ($iXReg << IOpcode::REG_UP_SHIFT) | $iYReg;
                    $aHandlers[$oCMPTemplate->iOpcode] = $this->compileTemplateHandler($oCMPTemplate);
                }
            }
        }
        $this->addExactHandlers($aHandlers);
    }

    /**
     * CMPI.bwl
     *
     */
    private function buildCMPIHandlers(array $aEAModes)
    {
        // CMPI byte
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_CMPI_B,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $this->iProgramCounter += ISize::WORD;
                    $iDst    = $oEAMode->readByte();
                    $iRes    = $iDst - $iSrc;
                    $this->updateCCRCMPByte($iSrc, $iDst, $iRes, false);
                }
            )
        );

        // CMPI word
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_CMPI_W,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readWord($this->iProgramCounter);
                    $this->iProgramCounter += ISize::WORD;
                    $iDst    = $oEAMode->readWord();
                    $iRes    = $iDst - $iSrc;
                    $this->updateCCRCMPWord($iSrc, $iDst, $iRes, false);
                }
            )
        );

        // CMPI long
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IArithmetic::OP_CMPI_L,
                    $aEAModes
                ),
                function(int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $iSrc    = $this->oOutside->readLong($this->iProgramCounter);
                    $this->iProgramCounter += ISize::LONG;
                    $iDst    = $oEAMode->readLong();
                    $iRes    = $iDst - $iSrc;
                    $this->updateCCRCMPLong($iSrc, $iDst, $iRes, false);
                }
            )
        );
    }
}
