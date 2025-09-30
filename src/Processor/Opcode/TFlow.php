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
            IPrefix::OP_RTR      => $cUnhandled,
        ]);


        // JMP
        $aCtrlModes = $this->generateForEAModeList(IEffectiveAddress::MODE_CONTROL);
        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IFlow::OP_JMP,
                    $aCtrlModes
                ),
                function(int $iOpcode) {
                    $oEAMode  = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter = $oEAMode->readLong();
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
                    $this->oAddressRegisters->iReg7 -= ISize::LONG;
                    $this->oAddressRegisters->iReg7 &= ISize::MASK_LONG;
                    $this->oOutside->writeLong(
                        $this->oAddressRegisters->iReg7,
                        $this->iProgramCounter
                    );
                    $oEAMode  = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter = $oEAMode->readLong();
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

        $this->buildBSRHandlers();
    }

    private function buildBSRHandlers()
    {
        $oBraTemplate = new Template\Params(
            IFlow::OP_BSR,
            'operation/Bcc/bsr',
            []
        );

        $aHandlers = [];
        // First special case handler for $00
        $aHandlers[IFlow::OP_BSR | 0x00] = $this->compileTemplateHandler($oBraTemplate);

        // Handlers for $01-7F are the same
        $oBraTemplate->iOpcode = IFlow::OP_BSR|0x01;
        $cBra = $this->compileTemplateHandler($oBraTemplate);
        for ($i = 0x01; $i < 0x80; ++$i) {
            $aHandlers[IFlow::OP_BSR | $i] = $cBra;
        }

        // Handlers for $80-$FE are the same
        $oBraTemplate->iOpcode = IFlow::OP_BSR|0x80;
        $cBra = $this->compileTemplateHandler($oBraTemplate);
        for ($i = 0x80; $i < 0xFF; ++$i) {
            $aHandlers[IFlow::OP_BSR | $i] = $cBra;
        }

        // Special case for $FF
        $oBraTemplate->iOpcode = IFlow::OP_BSR|0xFF;
        $aHandlers[IFlow::OP_BSR | 0xFF] = $this->compileTemplateHandler($oBraTemplate);
        $this->addExactHandlers($aHandlers);
    }

}
