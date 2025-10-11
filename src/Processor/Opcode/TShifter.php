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
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;

trait TShifter
{
    use Processor\TOpcode;

    protected function initShifterHandlers()
    {
        $this->buildASLShifterHandlers();
        $this->buildASRShifterHandlers();

        $this->buildLSLShifterHandlers();
        $this->buildLSRShifterHandlers();
        $this->buildROLShifterHandlers();
        $this->buildRORShifterHandlers();
        $this->buildROXLShifterHandlers();
        $this->buildROXRShifterHandlers();
    }

//     private function buildShifterHandlers(
//         string $sTemplate,
//         array  $aImmPrefixes,
//         array  $aDynPrefixes,
//         int    $iEAPrefix,
//         callable $cEAHandler
//     ) {
//         $this->buildShifterEAHandler($iEAPrefix, $cEAHandler);
//         $this->buildShifterImmHandlers($aImmPrefixes, $sTemplate);
//         $this->buildShifterDynHandlers($aDynPrefixes, $sTemplate);
//     }


    private function buildASLShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_ASL_M_W,
            function (int $iOpcode) {
                // Memory shifts are word sized, 1 bit at a time
                $oEAMode = $this->aDstEAModes[$iOpcode & 63];
                $iValue  = $oEAMode->readWord();
                $iResult = $iValue << 1;
                $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                $this->updateNZWord($iResult);
                $this->iConditionRegister |= (
                    ($iResult & 0x10000) ? IRegister::CCR_MASK_XC : 0
                ) | (
                    (($iResult ^ $iValue) & ISize::SIGN_BIT_WORD) >> 14 // Shift sign bit down into V position
                );
                $oEAMode->writeWord($iResult);
            }
        );

        $this->buildShifterImmHandlers(
            [
                IShifter::OP_ASL_ID_B,
                IShifter::OP_ASL_ID_W,
                IShifter::OP_ASL_ID_L,
            ],
            'asl'
        );

        $this->buildShifterDynHandlers(
            [
                IShifter::OP_ASL_DD_B,
                IShifter::OP_ASL_DD_W,
                IShifter::OP_ASL_DD_L,
            ],
            'asl'
        );
    }

    private function buildASRShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_ASR_M_W,
            function (int $iOpcode) {
                // Memory shifts are word sized, 1 bit at a time
                $oEAMode = $this->aDstEAModes[$iOpcode & 63];
                $iValue  = $oEAMode->readWord();
                $iLSB    = $iValue & 1;
                $iValue  = ($iValue & ISize::SIGN_BIT_WORD) | ($iValue >> 1);
                $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                $this->updateNZWord($iValue);
                // TODO Overflow
                $this->iConditionRegister |= (
                    $iLSB ? IRegister::CCR_MASK_XC : 0
                );
                $oEAMode->writeWord($iValue);
            }
        );

        $this->buildShifterImmHandlers(
            [
                IShifter::OP_ASR_ID_B,
                IShifter::OP_ASR_ID_W,
                IShifter::OP_ASR_ID_L,
            ],
            'asr'
        );

        $this->buildShifterDynHandlers(
            [
                IShifter::OP_ASR_DD_B,
                IShifter::OP_ASR_DD_W,
                IShifter::OP_ASR_DD_L,
            ],
            'asr'
        );
    }

    private function buildLSLShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_LSL_M_W,
            function (int $iOpcode) {
                // Memory shifts are word sized, 1 bit at a time
                $oEAMode = $this->aDstEAModes[$iOpcode & 63];
                $iValue  = $oEAMode->readWord() << 1;
                $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                $this->updateNZWord($iValue);
                $this->iConditionRegister |= (
                    ($iValue & 0x10000) ? IRegister::CCR_MASK_XC : 0
                );
                $oEAMode->writeWord($iValue);
            }
        );

        $this->buildShifterImmHandlers(
            [
                IShifter::OP_LSL_ID_B,
                IShifter::OP_LSL_ID_W,
                IShifter::OP_LSL_ID_L,
            ],
            'lsl'
        );

        $this->buildShifterDynHandlers(
            [
                IShifter::OP_LSL_DD_B,
                IShifter::OP_LSL_DD_W,
                IShifter::OP_LSL_DD_L,
            ],
            'lsl'
        );
    }

    private function buildLSRShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_LSR_M_W,
            function (int $iOpcode) {
                // Memory shifts are word sized, 1 bit at a time
                $oEAMode = $this->aDstEAModes[$iOpcode & 63];
                $iValue  = $oEAMode->readWord();
                $iLSB    = $iValue & 1;
                $iValue >>= 1;
                $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                $this->updateNZWord($iValue);
                $this->iConditionRegister |= (
                    $iLSB ? IRegister::CCR_MASK_XC : 0
                );
                $oEAMode->writeWord($iValue);
            }
        );

        $this->buildShifterImmHandlers(
            [
                IShifter::OP_LSR_ID_B,
                IShifter::OP_LSR_ID_W,
                IShifter::OP_LSR_ID_L,
            ],
            'lsr'
        );

        $this->buildShifterDynHandlers(
            [
                IShifter::OP_LSR_DD_B,
                IShifter::OP_LSR_DD_W,
                IShifter::OP_LSR_DD_L,
            ],
            'lsr'
        );
    }

    private function buildROLShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_ROL_M_W,
            function (int $iOpcode) {
                // Memory shifts are word sized, 1 bit at a time
                $oEAMode = $this->aDstEAModes[$iOpcode & 63];
                $iValue  = $oEAMode->readWord();
                $iValue  = ($iValue << 1) | ($iValue >> 15);
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->updateNZWord($iValue);
                $this->iConditionRegister |= (
                    ($iValue & 1)
                );
                $oEAMode->writeWord($iValue);
            }
        );
        $this->buildShifterImmHandlers(
            [
                IShifter::OP_ROL_ID_B,
                IShifter::OP_ROL_ID_W,
                IShifter::OP_ROL_ID_L,
            ],
            'rol'
        );
        $this->buildShifterDynHandlers(
            [
                IShifter::OP_ROL_DD_B,
                IShifter::OP_ROL_DD_W,
                IShifter::OP_ROL_DD_L,
            ],
            'rol'
        );
    }

    private function buildRORShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_ROR_M_W,
            function (int $iOpcode) {
                // Memory shifts are word sized, 1 bit at a time
                $oEAMode = $this->aDstEAModes[$iOpcode & 63];
                $iValue  = $oEAMode->readWord();
                $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                $this->iConditionRegister |= ($iValue & 1);
                $iValue  = ($iValue >> 1)|($iValue << 15);
                $this->updateNZWord($iValue);
                $oEAMode->writeWord($iValue);

            }
        );

        $this->buildShifterImmHandlers(
            [
                IShifter::OP_ROR_ID_B,
                IShifter::OP_ROR_ID_W,
                IShifter::OP_ROR_ID_L,
            ],
            'ror'
        );

        $this->buildShifterDynHandlers(
            [
                IShifter::OP_ROR_DD_B,
                IShifter::OP_ROR_DD_W,
                IShifter::OP_ROR_DD_L,
            ],
            'ror'
        );
    }


    private function buildROXLShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_ROXL_M_W,
            function (int $iOpcode) {
                // Memory shifts are word sized, 1 bit at a time
                $oEAMode = $this->aDstEAModes[$iOpcode & 63];
                $iValue  = $oEAMode->readWord();
                $iValue  = ($iValue << 1) | (($this->iConditionRegister & IRegister::CCR_EXTEND) >> 4);
                $this->iConditionRegister &= IRegister::CCR_CLEAR_XCV;
                $this->iConditionRegister |= (
                    ($iValue & 0x10000) ? IRegister::CCR_MASK_XC : 0
                );
                $this->updateNZWord($iValue);
                $oEAMode->writeWord($iValue);
            }
        );

        $this->buildShifterImmHandlers(
            [
                IShifter::OP_ROXL_ID_B,
                IShifter::OP_ROXL_ID_W,
                IShifter::OP_ROXL_ID_L,
            ],
            'roxl'
        );

        $this->buildShifterDynHandlers(
            [
                IShifter::OP_ROXL_DD_B,
                IShifter::OP_ROXL_DD_W,
                IShifter::OP_ROXL_DD_L,
            ],
            'roxl'
        );
    }

    private function buildROXRShifterHandlers()
    {
        $this->buildShifterEAHandler(
            IShifter::OP_ROXR_M_W,
            function (int $iOpcode) {
                // TODO
            }
        );

        $this->buildShifterImmHandlers(
            [
                IShifter::OP_ROXR_ID_B,
                IShifter::OP_ROXR_ID_W,
                IShifter::OP_ROXR_ID_L,
            ],
            'roxr'
        );

        $this->buildShifterDynHandlers(
            [
                IShifter::OP_ROXR_DD_B,
                IShifter::OP_ROXR_DD_W,
                IShifter::OP_ROXR_DD_L,
            ],
            'roxr'
        );
    }

    private function buildShifterEAHandler(int $iOpcode, callable $cHandler)
    {
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_MEM_ALTERABLE,
                    $iOpcode
                ),
                $cHandler
            )
        );
    }

    private function buildShifterImmHandlers(array $aPrefixes, string $sTemplate)
    {
        $oTemplate = new Template\Params(
            0,
            'operation/logic/' . $sTemplate . '_imm',
            []
        );
        $aHandlers = [];
        for ($iImmediate = 0; $iImmediate < 8; ++$iImmediate) {
            foreach ($aPrefixes as $iPrefix) {
                foreach(Processor\IRegister::DATA_REGS as $iReg) {
                    $oTemplate->iOpcode = $iOpcode = $iPrefix |
                        ($iImmediate << IOpcode::IMM_UP_SHIFT) |
                        $iReg;
                    $aHandlers[$iOpcode] = $this->compileTemplateHandler($oTemplate);
                }
            }
        }
        $this->addExactHandlers($aHandlers);
    }

    private function buildShifterDynHandlers(array $aPrefixes, string $sTemplate)
    {
        $oTemplate = new Template\Params(
            0,
            'operation/logic/' . $sTemplate . '_dyn',
            []
        );
        $aHandlers = [];
        foreach (Processor\IRegister::DATA_REGS as $iSrcReg) {
            foreach ($aPrefixes as $iPrefix) {
                foreach(Processor\IRegister::DATA_REGS as $iReg) {
                    $oTemplate->iOpcode = $iOpcode = $iPrefix |
                        ($iSrcReg << IOpcode::REG_UP_SHIFT) |
                        $iReg;
                    $aHandlers[$iOpcode] = $this->compileTemplateHandler($oTemplate);
                }
            }
        }
        $this->addExactHandlers($aHandlers);
    }
}
