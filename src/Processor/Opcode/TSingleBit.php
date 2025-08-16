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

trait TSingleBit
{
    use Processor\TOpcode;

    protected function initSingleBitHandlers()
    {
        $this->addPrefixHandlers([
            IPrefix::OP_BTST_D0 => function(int $iOpcode) { },
            IPrefix::OP_BTST_D1 => function(int $iOpcode) { },
            IPrefix::OP_BTST_D2 => function(int $iOpcode) { },
            IPrefix::OP_BTST_D3 => function(int $iOpcode) { },
            IPrefix::OP_BTST_D4 => function(int $iOpcode) { },
            IPrefix::OP_BTST_D5 => function(int $iOpcode) { },
            IPrefix::OP_BTST_D6 => function(int $iOpcode) { },
            IPrefix::OP_BTST_D7 => function(int $iOpcode) { },
            IPrefix::OP_BTST_I  => function(int $iOpcode) { },

            IPrefix::OP_BCHG_D0 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_D1 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_D2 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_D3 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_D4 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_D5 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_D6 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_D7 => function(int $iOpcode) { },
            IPrefix::OP_BCHG_I  => function(int $iOpcode) { },

            IPrefix::OP_BCLR_D0 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_D1 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_D2 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_D3 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_D4 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_D5 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_D6 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_D7 => function(int $iOpcode) { },
            IPrefix::OP_BCLR_I  => function(int $iOpcode) { },

            IPrefix::OP_BSET_D0 => function(int $iOpcode) { },
            IPrefix::OP_BSET_D1 => function(int $iOpcode) { },
            IPrefix::OP_BSET_D2 => function(int $iOpcode) { },
            IPrefix::OP_BSET_D3 => function(int $iOpcode) { },
            IPrefix::OP_BSET_D4 => function(int $iOpcode) { },
            IPrefix::OP_BSET_D5 => function(int $iOpcode) { },
            IPrefix::OP_BSET_D6 => function(int $iOpcode) { },
            IPrefix::OP_BSET_D7 => function(int $iOpcode) { },
            IPrefix::OP_BSET_I  => function(int $iOpcode) { },

        ]);
    }
}
