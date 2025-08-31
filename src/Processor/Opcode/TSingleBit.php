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
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\IRegister;

trait TSingleBit
{
    use Processor\TOpcode;

    protected function initSingleBitHandlers()
    {
        $this->buildSingleBitHandlers(
            ISingleBit::OP_BTST_DN,
            ISingleBit::OP_BTST_I,
            'btst'
        );
        $this->buildSingleBitHandlers(
            ISingleBit::OP_BCHG_DN,
            ISingleBit::OP_BCHG_I,
            'bchg'
        );
        $this->buildSingleBitHandlers(
            ISingleBit::OP_BCLR_DN,
            ISingleBit::OP_BCLR_I,
            'bclr'
        );
        $this->buildSingleBitHandlers(
            ISingleBit::OP_BSET_DN,
            ISingleBit::OP_BSET_I,
            'bset'
        );
    }

    private function buildSingleBitHandlers(int $iDynPrefix, int $iImmPrefix, string $sName)
    {
        $aHandlers = [];

        // Generate all the byte acessible EA modes we need here
        $aByteEAModes = $this->generateForEAModeList(
            [
                IEffectiveAddress::MODE_AI   => IRegister::ADDR_REGS,
                IEffectiveAddress::MODE_AIPI => IRegister::ADDR_REGS,
                IEffectiveAddress::MODE_AIPD => IRegister::ADDR_REGS,
                IEffectiveAddress::MODE_AID  => IRegister::ADDR_REGS,
                IEffectiveAddress::MODE_AII  => IRegister::ADDR_REGS,
                IEffectiveAddress::MODE_X    => [
                    IEffectiveAddress::MODE_X_SHORT,
                    IEffectiveAddress::MODE_X_LONG,
                    IEffectiveAddress::MODE_X_PC_D,
                    IEffectiveAddress::MODE_X_PC_X,
                    IEffectiveAddress::MODE_X_IMM
                ]
            ],
            0
        );

        $oBtstTemplate = new Template\Params(
            0,
            'operation/bit/' . $sName
        );

        foreach (IRegister::DATA_REGS as $iSourceReg) {
            $iPrefix = $iDynPrefix | ($iSourceReg << IOpcode::REG_UP_SHIFT);

            // Register targets are special, as they have direct 32-bit access
            foreach (IRegister::DATA_REGS as $iTargetReg) {
                $iOpcode = $iPrefix | $iTargetReg;
                $oBtstTemplate->iOpcode = $iOpcode;
                $aHandlers[$iOpcode] = $this->compileTemplateHandler($oBtstTemplate);
            }

            // All other supported EA modes can just use the EAMode logic
            $oBtstTemplate->iOpcode = $iPrefix | IOpcode::LSB_EA_A;
            $cEAHandler = $this->compileTemplateHandler($oBtstTemplate);
            foreach ($aByteEAModes as $iEAMode) {
                $aHandlers[$iPrefix|$iEAMode] = $cEAHandler;
            }

        }

        $iPrefix = $iImmPrefix;

        // Register targets are special, as they have direct 32-bit access
        foreach (IRegister::DATA_REGS as $iTargetReg) {
            $iOpcode = $iPrefix | $iTargetReg;
            $oBtstTemplate->iOpcode = $iOpcode;
            $aHandlers[$iOpcode] = $this->compileTemplateHandler($oBtstTemplate);
        }

        // All other supported EA modes can just use the EAMode logic
        $oBtstTemplate->iOpcode = $iPrefix | IOpcode::LSB_EA_A;
        $cEAHandler = $this->compileTemplateHandler($oBtstTemplate);
        foreach ($aByteEAModes as $iEAMode) {
            $aHandlers[$iPrefix|$iEAMode] = $cEAHandler;
        }
        $this->addExactHandlers($aHandlers);
    }
}
