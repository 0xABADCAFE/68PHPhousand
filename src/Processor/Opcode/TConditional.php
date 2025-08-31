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
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use LogicException;

trait TConditional
{
    use TOpcode;

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
        $this->buildSCCHandlers(IConditional::OP_SHI, 'shi');
        $this->buildSCCHandlers(IConditional::OP_SLS, 'sls');
        $this->buildSCCHandlers(IConditional::OP_SCC, 'scc');
        $this->buildSCCHandlers(IConditional::OP_SCS, 'scs');
        $this->buildSCCHandlers(IConditional::OP_SNE, 'sne');
        $this->buildSCCHandlers(IConditional::OP_SEQ, 'seq');
        $this->buildSCCHandlers(IConditional::OP_SVC, 'svc');
        $this->buildSCCHandlers(IConditional::OP_SVS, 'svs');
        $this->buildSCCHandlers(IConditional::OP_SPL, 'spl');
        $this->buildSCCHandlers(IConditional::OP_SMI, 'smi');
        $this->buildSCCHandlers(IConditional::OP_SGE, 'sge');
        $this->buildSCCHandlers(IConditional::OP_SLT, 'slt');
        $this->buildSCCHandlers(IConditional::OP_SGT, 'sgt');
        $this->buildSCCHandlers(IConditional::OP_SLE, 'sle');

        $this->buildDBCCHandlers(IConditional::OP_DBT,  'dbt');
        $this->buildDBCCHandlers(IConditional::OP_DBF,  'dbf');
        $this->buildDBCCHandlers(IConditional::OP_DBHI, 'dbhi');
        $this->buildDBCCHandlers(IConditional::OP_DBLS, 'dbls');
        $this->buildDBCCHandlers(IConditional::OP_DBCC, 'dbcc');
        $this->buildDBCCHandlers(IConditional::OP_DBCS, 'dbcs');
        $this->buildDBCCHandlers(IConditional::OP_DBNE, 'dbne');
        $this->buildDBCCHandlers(IConditional::OP_DBEQ, 'dbeq');
        $this->buildDBCCHandlers(IConditional::OP_DBVC, 'dbvc');
        $this->buildDBCCHandlers(IConditional::OP_DBVS, 'dbvs');
        $this->buildDBCCHandlers(IConditional::OP_DBPL, 'dbpl');
        $this->buildDBCCHandlers(IConditional::OP_DBMI, 'dbmi');
        $this->buildDBCCHandlers(IConditional::OP_DBGE, 'dbge');
        $this->buildDBCCHandlers(IConditional::OP_DBLT, 'dblt');
        $this->buildDBCCHandlers(IConditional::OP_DBGT, 'dbgt');
        $this->buildDBCCHandlers(IConditional::OP_DBLE, 'dble');

    }


    private function buildBCCHandlers(int $iPrefix, string $sName)
    {
        $oBraTemplate = new Template\Params(
            $iPrefix,
            'operation/Bcc/'.$sName,
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

    private function buildDBCCHandlers(int $iPrefix, string $sName)
    {
        $oSccTemplate = new Template\Params(
            $iPrefix,
            'operation/DBcc/'.$sName,
            []
        );
        $cHandler = $this->compileTemplateHandler($oSccTemplate);
        $aHandlers = array_fill_keys(range($iPrefix, $iPrefix|7, 1), $cHandler);
        $this->addExactHandlers($aHandlers);
    }
}
