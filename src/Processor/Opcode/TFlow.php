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
            IPrefix::OP_RTS      => $cUnhandled,
            IPrefix::OP_TRAPV    => $cUnhandled,
            IPrefix::OP_RTR      => $cUnhandled,
        ]);

        $this->addExactHandlers(
            array_fill_keys(
                $this->mergePrefixForModeList(
                    IFlow::OP_JMP,
                    $this->generateForEAModeList(IEffectiveAddress::MODE_CONTROL)
                ),
                function(int $iOpcode) {
                    $oEAMode  = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iProgramCounter = $oEAMode->readLong();
                }
            )
        );


    }

}
