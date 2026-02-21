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
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\IRegister;

use LogicException;

trait TFlow
{
    use Processor\TOpcode;

    private Processor\Halted $oHalt;

    protected function initFlowHandlers()
    {
        $this->oHalt = new Processor\Halted();

        $cUnhandled = function(int $iOpcode) {
            throw new LogicException(sprintf('Unhandled flow operation 0x%4X (TODO)', $iOpcode));
        };

        $this->addExactHandlers([
            IPrefix::OP_STOP => function(int $iOpcode) {
                $iStatusCCR = $iStop = $this->oOutside->readWord(
                    $this->iProgramCounter
                ) & IRegister::SR_CCR_MASK;
                $this->iConditionRegister = $iStatusCCR & 0xFF;
                $this->iStatusRegister    = ($iStatusCCR >> 8);
                $this->oHalt->raise($iStop);
            },

            IPrefix::OP_RTE => function (int $iOpcode) {
                $iSP = &$this->oAddressRegisters->iReg7;
                $iStatusCCR = $this->oOutside->readWord(
                    $iSP
                );
                $this->iConditionRegister = $iStatusCCR & 0xFF;
                $this->iStatusRegister    = ($iStatusCCR >> 8);
                $this->iProgramCounter = $this->oOutside->readLong(($iSP + ISize::WORD) & ISize::MASK_LONG);
                $iSP = ($iSP + 6) & ISize::MASK_LONG;
            },

            IPrefix::OP_RTR => function (int $iOpcode) {
                $iSP = &$this->oAddressRegisters->iReg7;
                $this->iConditionRegister = $this->oOutside->readWord($iSP) & IRegister::CCR_MASK;
                $this->iProgramCounter    = $this->oOutside->readLong(($iSP + ISize::WORD) & ISize::MASK_LONG);
                $iSP = ($iSP + 6) & ISize::MASK_LONG;
            },
        ]);

        $this->buildBranchHandlers(IFlow::OP_BSR, 'bsr');
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

        $this->buildDBCCHandlers(IFlow::OP_DBT,  'dbt');
        $this->buildDBCCHandlers(IFlow::OP_DBF,  'dbf');
        $this->buildDBCCHandlers(IFlow::OP_DBHI, 'dbhi');
        $this->buildDBCCHandlers(IFlow::OP_DBLS, 'dbls');
        $this->buildDBCCHandlers(IFlow::OP_DBCC, 'dbcc');
        $this->buildDBCCHandlers(IFlow::OP_DBCS, 'dbcs');
        $this->buildDBCCHandlers(IFlow::OP_DBNE, 'dbne');
        $this->buildDBCCHandlers(IFlow::OP_DBEQ, 'dbeq');
        $this->buildDBCCHandlers(IFlow::OP_DBVC, 'dbvc');
        $this->buildDBCCHandlers(IFlow::OP_DBVS, 'dbvs');
        $this->buildDBCCHandlers(IFlow::OP_DBPL, 'dbpl');
        $this->buildDBCCHandlers(IFlow::OP_DBMI, 'dbmi');
        $this->buildDBCCHandlers(IFlow::OP_DBGE, 'dbge');
        $this->buildDBCCHandlers(IFlow::OP_DBLT, 'dblt');
        $this->buildDBCCHandlers(IFlow::OP_DBGT, 'dbgt');
        $this->buildDBCCHandlers(IFlow::OP_DBLE, 'dble');

        // JMP
        $aCtrlModes = $this->generateForEAModeList(IEffectiveAddress::MODE_CONTROL);
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IFlow::OP_JMP,
                    $aCtrlModes
                ),
                function(int $iOpcode) {
                    $oEAMode  = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter = $oEAMode->getAddress();
                }
            )
        );

        // JSR
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IFlow::OP_JSR,
                    $aCtrlModes
                ),
                function(int $iOpcode) {
                    $iNewPC = $this->aSrcEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA]->getAddress();
                    $this->oAddressRegisters->iReg7 -= ISize::LONG;
                    $this->oAddressRegisters->iReg7 &= ISize::MASK_LONG;
                    $this->oOutside->writeLong(
                        $this->oAddressRegisters->iReg7,
                        $this->iProgramCounter
                    );
                    $this->iProgramCounter = $iNewPC;
                }
            )
        );

        // OP_RTS
        $this->addExactHandlers([
            IFlow::OP_RTS => function(int $iOpcode) {
                $this->iProgramCounter = $this->oOutside->readLong(
                    $this->oAddressRegisters->iReg7
                );
                $this->oAddressRegisters->iReg7 += ISize::LONG;
                $this->oAddressRegisters->iReg7 &= ISize::MASK_LONG;
            }
        ]);

    }

    private function buildBranchHandlers(int $iPrefix, string $sName)
    {
        $oBraTemplate = new Template\Params(
            $iPrefix,
            'operation/Bcc/'.$sName,
            []
        );

        $aHandlers = [];
        // First special case handler for $00
        $aHandlers[$iPrefix | 0x00] = $this->compileTemplateHandler($oBraTemplate);

        // Handlers for $01-7F are the same
        $oBraTemplate->iOpcode = $iPrefix|0x01;
        $cBra = $this->compileTemplateHandler($oBraTemplate);
        for ($i = 0x01; $i < 0x80; ++$i) {
            $aHandlers[$iPrefix | $i] = $cBra;
        }

        // Handlers for $80-$FE are the same
        $oBraTemplate->iOpcode = $iPrefix|0x80;
        $cBra = $this->compileTemplateHandler($oBraTemplate);
        for ($i = 0x80; $i < 0xFF; ++$i) {
            $aHandlers[$iPrefix | $i] = $cBra;
        }

        // Special case for $FF
        $oBraTemplate->iOpcode = $iPrefix|0xFF;
        $aHandlers[$iPrefix | 0xFF] = $this->compileTemplateHandler($oBraTemplate);
        $this->addExactHandlers($aHandlers);
    }


    private function buildDBCCHandlers(int $iPrefix, string $sName)
    {
        $oSccTemplate = new Template\Params(
            $iPrefix,
            'operation/DBcc/'.$sName,
            []
        );
        $cHandler = $this->compileTemplateHandler($oSccTemplate);
        $aHandlers = array_fill_keys(range($iPrefix, $iPrefix + 7, 1), $cHandler);
        $this->addExactHandlers($aHandlers);
    }
}
