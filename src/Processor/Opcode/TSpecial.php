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

trait TSpecial
{
    use Processor\TOpcode;

    protected function initSpecialHandlers()
    {
        $cUnhandled = function(int $iOpcode) {
            throw new LogicException(sprintf('Unhandled special operation 0x%4X (TODO)', $iOpcode));
        };

        $this->addExactHandlers([
            IPrefix::OP_ILLEGAL  => $cUnhandled,

            IPrefix::OP_RESET    => function() {
                // TODO - probably needs to be a bit more specific than this
                $this->reset();
            },

            IPrefix::OP_NOP      => function() {
                // Nothing yet
            },

        ]);

    }
}
