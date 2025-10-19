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

    protected function initFlowHandlers()
    {
        $cUnhandled = function(int $iOpcode) {
            throw new LogicException(sprintf('Unhandled flow operation 0x%4X (TODO)', $iOpcode));
        };

        $this->addExactHandlers([
            IPrefix::OP_STOP     => $cUnhandled,
            IPrefix::OP_RTE      => $cUnhandled,
            IPrefix::OP_TRAPV    => $cUnhandled,
            IPrefix::OP_RTR      => function (int $iOpcode) {
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

        // 68020+ Enhanced Flow Control
        if ($this->iProcessorModel >= IProcessorModel::MC68020) {
            $this->init68020FlowHandlers();
        }

    }

    /**
     * Initialize 68020+ flow control handlers
     */
    private function init68020FlowHandlers(): void
    {
        // RTD - Return and Deallocate
        $this->addExactHandlers([
            IFlow::OP_RTD => function(int $iOpcode) {
                // Read displacement
                $iDisplacement = Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
                $this->iProgramCounter += ISize::WORD;

                // Pop return address
                $this->iProgramCounter = $this->oOutside->readLong(
                    $this->oAddressRegisters->iReg7
                );

                // Deallocate stack space (SP = SP + 4 + displacement)
                $this->oAddressRegisters->iReg7 += ISize::LONG + $iDisplacement;
                $this->oAddressRegisters->iReg7 &= ISize::MASK_LONG;
            }
        ]);

        // LINK.L - Link with 32-bit displacement
        $this->addExactHandlers(
            array_fill_keys(
                range(
                    IFlow::OP_LINK_L | IRegister::A0,
                    IFlow::OP_LINK_L | IRegister::A7
                ),
                function (int $iOpcode) {
                    $iSP  = &$this->oAddressRegisters->iReg7;
                    $iReg = &$this->oAddressRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG];

                    // Push An
                    $iSP -= ISize::LONG;
                    $iSP &= ISize::MASK_LONG;
                    $this->oOutside->writeLong($iSP, $iReg);

                    // An = SP
                    $iReg = $iSP;

                    // SP = SP + displacement (32-bit)
                    $iDisplacement = Sign::extLong($this->oOutside->readLong($this->iProgramCounter));
                    $this->iProgramCounter += ISize::LONG;
                    $iSP += $iDisplacement;
                    $iSP &= ISize::MASK_LONG;
                }
            )
        );

        // BKPT - Breakpoint (stub for now)
        $this->addExactHandlers(
            array_fill_keys(
                range(IFlow::OP_BKPT, IFlow::OP_BKPT | 0x7),
                function(int $iOpcode) {
                    $iVector = $iOpcode & 0x7;
                    throw new LogicException(sprintf('BKPT #%d not implemented', $iVector));
                }
            )
        );

        // TRAPcc - Conditional Trap
        $this->buildTRAPccHandlers(IFlow::OP_TRAPT,  IOpcode::CC_T);   // Always
        $this->buildTRAPccHandlers(IFlow::OP_TRAPF,  IOpcode::CC_F);   // Never
        $this->buildTRAPccHandlers(IFlow::OP_TRAPHI, IOpcode::CC_HI);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPLS, IOpcode::CC_LS);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPCC, IOpcode::CC_CC);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPCS, IOpcode::CC_CS);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPNE, IOpcode::CC_NE);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPEQ, IOpcode::CC_EQ);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPVC, IOpcode::CC_VC);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPVS, IOpcode::CC_VS);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPPL, IOpcode::CC_PL);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPMI, IOpcode::CC_MI);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPGE, IOpcode::CC_GE);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPLT, IOpcode::CC_LT);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPGT, IOpcode::CC_GT);
        $this->buildTRAPccHandlers(IFlow::OP_TRAPLE, IOpcode::CC_LE);
    }

    /**
     * Build TRAPcc handlers for a specific condition code
     */
    private function buildTRAPccHandlers(int $iOpcode, int $iCondition): void
    {
        // TRAPcc.W with word operand (opcode | 0x2)
        $this->aExactHandler[$iOpcode | 0x2] = function(int $iOpcode) use ($iCondition) {
            if ($this->testCondition($iCondition)) {
                // Read and discard operand word
                $this->iProgramCounter += ISize::WORD;
                throw new LogicException('TRAP exception (TRAPcc)');
            } else {
                // Skip operand word
                $this->iProgramCounter += ISize::WORD;
            }
        };

        // TRAPcc.L with long operand (opcode | 0x3)
        $this->aExactHandler[$iOpcode | 0x3] = function(int $iOpcode) use ($iCondition) {
            if ($this->testCondition($iCondition)) {
                // Read and discard operand long
                $this->iProgramCounter += ISize::LONG;
                throw new LogicException('TRAP exception (TRAPcc)');
            } else {
                // Skip operand long
                $this->iProgramCounter += ISize::LONG;
            }
        };

        // TRAPcc with no operand (opcode | 0x4)
        $this->aExactHandler[$iOpcode | 0x4] = function(int $iOpcode) use ($iCondition) {
            if ($this->testCondition($iCondition)) {
                throw new LogicException('TRAP exception (TRAPcc)');
            }
        };
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
