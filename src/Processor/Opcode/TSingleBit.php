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
use ABadCafe\G8PHPhousand\Processor\Opcode;

trait TSingleBit
{
    use Processor\TOpcode;

    protected function initSingleBitHandlers()
    {
        $this->buildBTSTHandlers(
            ISingleBit::OP_BTST_DN,
            ISingleBit::OP_BTST_I,
            'btst'
        );
        $this->buildBTSTHandlers(
            ISingleBit::OP_BCHG_DN,
            ISingleBit::OP_BCHG_I,
            'bchg'
        );
        $this->buildBTSTHandlers(
            ISingleBit::OP_BCLR_DN,
            ISingleBit::OP_BCLR_I,
            'bclr'
        );
        $this->buildBTSTHandlers(
            ISingleBit::OP_BSET_DN,
            ISingleBit::OP_BSET_I,
            'bset'
        );
    }

    private function buildBTSTHandlers(int $iDynPrefix, int $iImmPrefix, string $sName)
    {
        $aHandlers = [];

        // Generate all the byte acessible EA modes we need here
        $aByteEAModes = $this->generateForEAModeList(
            [
                Processor\IEffectiveAddress::MODE_AI   => Processor\IRegister::ADDR_REGS,
                Processor\IEffectiveAddress::MODE_AIPI => Processor\IRegister::ADDR_REGS,
                Processor\IEffectiveAddress::MODE_AIPD => Processor\IRegister::ADDR_REGS,
                Processor\IEffectiveAddress::MODE_AID  => Processor\IRegister::ADDR_REGS,
                Processor\IEffectiveAddress::MODE_AII  => Processor\IRegister::ADDR_REGS,
                Processor\IEffectiveAddress::MODE_X    => [
                    Processor\IEffectiveAddress::MODE_X_SHORT,
                    Processor\IEffectiveAddress::MODE_X_LONG,
                    Processor\IEffectiveAddress::MODE_X_PC_D,
                    Processor\IEffectiveAddress::MODE_X_PC_X,
                    Processor\IEffectiveAddress::MODE_X_IMM
                ]
            ],
            0
        );

        $oBtstTemplate = new Template\Params(
            0,
            'operation/bit/' . $sName,
            []
        );

        foreach (Processor\IRegister::DATA_REGS as $iSourceReg) {
            $iPrefix = $iDynPrefix | ($iSourceReg << 9);

            // Register targets are special, as they have direct 32-bit access
            foreach (Processor\IRegister::DATA_REGS as $iTargetReg) {
                $iOpcode = $iPrefix | $iTargetReg;
                $oBtstTemplate->iOpcode = $iOpcode;
                $aHandlers[$iOpcode] = $this->compileTemplateHandler($oBtstTemplate);
            }

            // All other supported EA modes can just use the EAMode logic
            $oBtstTemplate->iOpcode = $iPrefix | Processor\IOpcode::LSB_EA_A;
            $cEAHandler = $this->compileTemplateHandler($oBtstTemplate);
            foreach ($aByteEAModes as $iEAMode) {
                $aHandlers[$iPrefix|$iEAMode] = $cEAHandler;
            }

        }

        $iPrefix = $iImmPrefix;

        // Register targets are special, as they have direct 32-bit access
        foreach (Processor\IRegister::DATA_REGS as $iTargetReg) {
            $iOpcode = $iPrefix | $iTargetReg;
            $oBtstTemplate->iOpcode = $iOpcode;
            $aHandlers[$iOpcode] = $this->compileTemplateHandler($oBtstTemplate);
        }

        // All other supported EA modes can just use the EAMode logic
        $oBtstTemplate->iOpcode = $iPrefix | Processor\IOpcode::LSB_EA_A;
        $cEAHandler = $this->compileTemplateHandler($oBtstTemplate);
        foreach ($aByteEAModes as $iEAMode) {
            $aHandlers[$iPrefix|$iEAMode] = $cEAHandler;
        }
        $this->addExactHandlers($aHandlers);
    }
}
