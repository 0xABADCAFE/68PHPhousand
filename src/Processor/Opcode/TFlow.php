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

trait TFlow
{
    use Processor\TOpcode;

    protected function initFlowHandlers()
    {
        $cUnhandled = function(int $iOpcode) {
            throw new LogicException(sprintf('Unhandled flow operation 0x%4X (TODO)', $iOpcode));
        };

        $this->addExactHandlers([
            IPrefix::OP_STOP     => $cUnhandled,
            IPrefix::OP_RTE      => $cUnhandled,
            IPrefix::OP_RTS      => $cUnhandled,
            IPrefix::OP_TRAPV    => $cUnhandled,
            IPrefix::OP_RTR      => $cUnhandled,
        ]);

        $this->buildBranchHandlers(IFlow::OP_BRA, 'bra');
        $this->buildBranchHandlers(IFlow::OP_BHI, 'bhi');
        $this->buildBranchHandlers(IFlow::OP_BLS, 'bls');
        $this->buildBranchHandlers(IFlow::OP_BCC, 'bcc');
        $this->buildBranchHandlers(IFlow::OP_BCS, 'bcs');
        $this->buildBranchHandlers(IFlow::OP_BNE, 'bne');
        $this->buildBranchHandlers(IFlow::OP_BEQ, 'beq');
        $this->buildBranchHandlers(IFlow::OP_BVC, 'bvc');
        $this->buildBranchHandlers(IFlow::OP_BVS, 'bvs');
        $this->buildBranchHandlers(IFlow::OP_BPL, 'bpl');
        $this->buildBranchHandlers(IFlow::OP_BMI, 'bmi');
        $this->buildBranchHandlers(IFlow::OP_BGE, 'bge');
        $this->buildBranchHandlers(IFlow::OP_BLT, 'blt');
        $this->buildBranchHandlers(IFlow::OP_BGT, 'bgt');
        $this->buildBranchHandlers(IFlow::OP_BLE, 'ble');
    }

    private function buildBranchHandlers(int $iPrefix, string $sName)
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
}
