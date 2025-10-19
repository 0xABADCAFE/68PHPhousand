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
use ABadCafe\G8PHPhousand\Device;

use LogicException;

/**
 * TControlRegister
 *
 * Implements control register operations for 68010+
 *
 * Instructions:
 *   - MOVEC Rc,Rn (move from control register to general register)
 *   - MOVEC Rn,Rc (move from general register to control register)
 *   - MOVES (move to/from address space with function code - stubbed)
 */
trait TControlRegister
{
    /**
     * Initialize MOVEC and MOVES instruction handlers
     *
     * MOVEC opcodes:
     *   0x4E7B - MOVEC Rn,Rc (general -> control)
     *   0x4E7A - MOVEC Rc,Rn (control -> general)
     *
     * MOVES opcode prefix:
     *   0x0E00-0x0FFF - MOVES (move to/from address space with function code)
     */
    private function initControlRegisterHandlers(): void
    {
        // MOVES - Move to/from address space (68010+)
        // This is a stub implementation - MOVES uses SFC/DFC for memory access
        // Full implementation would require function code based bus access
        $this->aPrefixHandler[0x0E00] = [
            'mask' => 0xFF00,
            'handler' => function(int $iOpcode): int {
                // Privilege check - MOVES is supervisor only
                assert(
                    $this->isSupervisor(),
                    new LogicException('MOVES requires supervisor mode')
                );

                // MOVES stub - not yet fully implemented
                // Would need to:
                // 1. Read extension word to get register and direction
                // 2. Use SFC (source) or DFC (destination) function code
                // 3. Perform memory access with function code
                throw new LogicException(
                    sprintf('MOVES instruction not yet fully implemented (opcode $%04X)', $iOpcode)
                );
            }
        ];

        // MOVEC Rn,Rc - Move to control register
        $this->aExactHandler[0x4E7B] = function (): int {
            // Privilege check - MOVEC is supervisor only
            assert(
                $this->isSupervisor(),
                new LogicException('MOVEC requires supervisor mode')
            );

            // Read extension word
            $iExtension = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += Processor\ISize::WORD;

            // Extract control register code (bits 11-0)
            $iControlReg = $iExtension & 0x0FFF;

            // Extract general register (bits 15-12)
            $bIsAddress = 0 !== ($iExtension & 0x8000);
            $iRegNum = ($iExtension >> 12) & 0x7;

            // Get value from general register
            $iValue = $bIsAddress
                ? $this->oAddressRegisters->aIndex[$iRegNum]
                : $this->oDataRegisters->aIndex[$iRegNum];

            // Write to control register
            $this->setControlRegister($iControlReg, $iValue);

            return 12; // Base timing (not cycle-accurate)
        };

        // MOVEC Rc,Rn - Move from control register
        $this->aExactHandler[0x4E7A] = function (): int {
            // Privilege check - MOVEC is supervisor only
            assert(
                $this->isSupervisor(),
                new LogicException('MOVEC requires supervisor mode')
            );

            // Read extension word
            $iExtension = $this->oOutside->readWord($this->iProgramCounter);
            $this->iProgramCounter += Processor\ISize::WORD;

            // Extract control register code (bits 11-0)
            $iControlReg = $iExtension & 0x0FFF;

            // Extract general register (bits 15-12)
            $bIsAddress = 0 !== ($iExtension & 0x8000);
            $iRegNum = ($iExtension >> 12) & 0x7;

            // Read from control register
            $iValue = $this->getControlRegister($iControlReg);

            // Write to general register
            if ($bIsAddress) {
                $this->oAddressRegisters->aIndex[$iRegNum] = $iValue;
            } else {
                $this->oDataRegisters->aIndex[$iRegNum] = $iValue;
            }

            return 12; // Base timing (not cycle-accurate)
        };
    }

    /**
     * Check if processor is in supervisor mode
     */
    private function isSupervisor(): bool
    {
        return 0 !== ($this->iStatusRegister & Processor\IRegister::SR_MASK_SUPER);
    }
}
