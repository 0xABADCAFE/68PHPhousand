<?php

/**
 *       _/_/_/    _/_/    _/_/_/   _/    _/  _/_/_/   _/                                                            _/
 *     _/       _/    _/  _/    _/ _/    _/  _/    _/ _/_/_/     _/_/   _/    _/   _/_/_/    _/_/_/  _/_/_/     _/_/_/
 *    _/_/_/     _/_/    _/_/_/   _/_/_/_/  _/_/_/   _/    _/ _/    _/ _/    _/ _/_/      _/    _/  _/    _/ _/    _/
 *   _/    _/ _/    _/  _/       _/    _/  _/       _/    _/ _/    _/ _/    _/     _/_/  _/    _/  _/    _/ _/    _/
 *    _/_/_/     _/_/    _/       _/    _/  _/       _/    _/   _/_/    _/_/_/  _/_/_/      _/_/_/  _/    _/   _/_/_/
 *
 *   >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Damn you, linkedin, what have you started ? <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 */

declare(strict_types=1);

namespace ABadCafe\G8PHPhousand\Processor\Opcode;

use LogicException;

/**
 * TCoprocessor
 *
 * Implements coprocessor interface for 68020+
 *
 * All F-line opcodes ($Fxxx) generate F-line emulator exceptions (vector 11).
 * This allows software to emulate coprocessor instructions or attach actual
 * coprocessor implementations (e.g., 68881/68882 FPU).
 */
trait TCoprocessor
{
    /**
     * Initialize coprocessor (F-line) exception handlers
     *
     * All opcodes from $F000-$FFFF trigger F-line emulator exception.
     * This is exception vector 11.
     */
    private function initCoprocessorHandlers(): void
    {
        // F-line opcodes: $F000-$FFFF (all opcodes starting with 1111)
        // Generate F-line emulator exception (vector 11)
        $cFLineHandler = function(int $iOpcode): void {
            throw new LogicException(
                sprintf(
                    'F-line emulator exception (coprocessor opcode $%04X, vector 11)',
                    $iOpcode
                )
            );
        };

        // Register handler for all $Fxxx opcodes
        for ($iOpcode = 0xF000; $iOpcode <= 0xFFFF; $iOpcode++) {
            $this->aExactHandler[$iOpcode] = $cFLineHandler;
        }
    }
}
