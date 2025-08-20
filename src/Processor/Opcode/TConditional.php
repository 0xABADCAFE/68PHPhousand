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

trait TConditional
{
    use Processor\TOpcode;

    protected function initConditionalHandlers()
    {
        $this->buildBCCHandlers(IConditional::OP_BRA, 'bra');
        $this->buildBCCHandlers(IConditional::OP_BHI, 'bhi');
        $this->buildBCCHandlers(IConditional::OP_BLS, 'bls');
        $this->buildBCCHandlers(IConditional::OP_BCC, 'bcc');
        $this->buildBCCHandlers(IConditional::OP_BCS, 'bcs');
        $this->buildBCCHandlers(IConditional::OP_BNE, 'bne');
        $this->buildBCCHandlers(IConditional::OP_BEQ, 'beq');
        $this->buildBCCHandlers(IConditional::OP_BVC, 'bvc');
        $this->buildBCCHandlers(IConditional::OP_BVS, 'bvs');
        $this->buildBCCHandlers(IConditional::OP_BPL, 'bpl');
        $this->buildBCCHandlers(IConditional::OP_BMI, 'bmi');
        $this->buildBCCHandlers(IConditional::OP_BGE, 'bge');
        $this->buildBCCHandlers(IConditional::OP_BLT, 'blt');
        $this->buildBCCHandlers(IConditional::OP_BGT, 'bgt');
        $this->buildBCCHandlers(IConditional::OP_BLE, 'ble');

        $this->buildSCCHandlers(IConditional::OP_ST,  'st');
        $this->buildSCCHandlers(IConditional::OP_SF,  'sf');
        $this->buildSCCHandlers(IConditional::OP_BHI, 'shi');
        $this->buildSCCHandlers(IConditional::OP_BLS, 'sls');
        $this->buildSCCHandlers(IConditional::OP_BCC, 'scc');
        $this->buildSCCHandlers(IConditional::OP_BCS, 'scs');
        $this->buildSCCHandlers(IConditional::OP_BNE, 'sne');
        $this->buildSCCHandlers(IConditional::OP_BEQ, 'seq');
        $this->buildSCCHandlers(IConditional::OP_BVC, 'svc');
        $this->buildSCCHandlers(IConditional::OP_BVS, 'svs');
        $this->buildSCCHandlers(IConditional::OP_BPL, 'spl');
        $this->buildSCCHandlers(IConditional::OP_BMI, 'smi');
        $this->buildSCCHandlers(IConditional::OP_BGE, 'sge');
        $this->buildSCCHandlers(IConditional::OP_BLT, 'slt');
        $this->buildSCCHandlers(IConditional::OP_BGT, 'sgt');
        $this->buildSCCHandlers(IConditional::OP_BLE, 'sle');
    }


    private function buildBCCHandlers(int $iPrefix, string $sName)
    {
        $oBraTemplate = new Template\Params(
            $iPrefix,
            'operation/'.$sName,
            []
        );

        $aHandlers = [];
        // First special case handler for $00
        $aHandlers[$iPrefix|0x00] = $this->compileTemplateHandler($oBraTemplate);

        // Handlers for $01-7F are the same
        $oBraTemplate->iOpcode = $iPrefix|0x01;
        $cBra = $this->compileTemplateHandler($oBraTemplate);
        for ($i = 0x01; $i < 0x80; ++$i) {
            $aHandlers[$iPrefix|$i] = $cBra;
        }

        // Handlers for $80-$FE are the same
        $oBraTemplate->iOpcode = $iPrefix|0x80;
        $cBra = $this->compileTemplateHandler($oBraTemplate);
        for ($i = 0x80; $i < 0xFF; ++$i) {
            $aHandlers[$iPrefix|$i] = $cBra;
        }

        // Special case for $FF
        $oBraTemplate->iOpcode = $iPrefix|0xFF;
        $aHandlers[$iPrefix|0xFF] = $this->compileTemplateHandler($oBraTemplate);
        $this->addExactHandlers($aHandlers);
    }

    private function buildSCCHandlers(int $iPrefix, string $sName)
    {

    }

}
