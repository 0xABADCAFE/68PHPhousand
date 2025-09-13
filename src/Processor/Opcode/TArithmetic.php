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
        $this->buildADDQHandlers($aEAModes);
        $this->buildSUBIHandlers($aEAModes);
        $this->buildSUBQHandlers($aEAModes);
        $this->buildADDEA2DHandlers();
        $this->buildADDD2EAHandlers();
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
                    $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue -= $oEAMode->readByte();
                    $this->iConditionRegister &= Processor\IRegister::CCR_CLEAR_CV;

                    // TODO - C and V flags
                    $this->updateNZByte($iValue);
                    $oEAMode->writeByte($iValue);
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
                    $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue -= $oEAMode->readWord();
                    $this->iConditionRegister &= Processor\IRegister::CCR_CLEAR_CV;

                    // TODO - C and V flags
                    $this->updateNZWord($iValue);
                    $oEAMode->writeWord($iValue);
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
                    $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::LONG;
                    $iValue -= $oEAMode->readLong();
                    $this->iConditionRegister &= Processor\IRegister::CCR_CLEAR_CV;

                    // TODO - C and V flags
                    $this->updateNZLong($iValue);
                    $oEAMode->writeLong($iValue);
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
                    $iValue  = $this->oOutside->readByte($this->iProgramCounter + ISize::BYTE);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue += $oEAMode->readByte();
                    $this->iConditionRegister &= Processor\IRegister::CCR_CLEAR_CV;

                    // TODO - C and V flags
                    $this->updateNZByte($iValue);
                    $oEAMode->writeByte($iValue);
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
                    $iValue  = $this->oOutside->readWord($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::WORD;
                    $iValue += $oEAMode->readWord();
                    $this->iConditionRegister &= Processor\IRegister::CCR_CLEAR_CV;

                    // TODO - C and V flags
                    $this->updateNZWord($iValue);
                    $oEAMode->writeWord($iValue);
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
                    $iValue  = $this->oOutside->readLong($this->iProgramCounter);
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter += ISize::LONG;
                    $iValue += $oEAMode->readLong();
                    $this->iConditionRegister &= Processor\IRegister::CCR_CLEAR_CV;

                    // TODO - C and V flags
                    $this->updateNZLong($iValue);
                    $oEAMode->writeLong($iValue);
                }
            )
        );
    }

    private function buildADDQHandlers(array $aEAModes)
    {
        $oADDQTemplate = new Template\Params(
            0,
            'operation/arithmetic/addq',
            []
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
    }

    private function buildSUBQHandlers(array $aEAModes)
    {
        $oSUBQTemplate = new Template\Params(
            0,
            'operation/arithmetic/subq',
            []
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
    }

    private function buildADDEA2DHandlers()
    {
        $aEAModes = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_ALL_EXCEPT_AREGS
        );

        $aEAAregs = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_ONLY_AREGS
        );

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

    private function buildADDD2EAHandlers()
    {
        $aEAModes = $this->generateForEAModeList(
            Processor\IEffectiveAddress::MODE_MEM_ALTERABLE
        );

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
}
