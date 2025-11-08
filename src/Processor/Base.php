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

namespace ABadCafe\G8PHPhousand\Processor;

use ABadCafe\G8PHPhousand\I68KProcessor;
use ABadCafe\G8PHPhousand\Device;

use LogicException;

/**
 * Base class implementation
 */
abstract class Base implements I68KProcessor, IOpcode, Opcode\IPrefix
{
    protected Device\IBus $oOutside;

    use TRegisterUnit;
    use TAddressUnit;
    use TCache;

    use Fault\TProcess; // Faulty process, lol

    use Opcode\Template\TGenerator;
    use Opcode\TMove;
    use Opcode\TLogical;
    use Opcode\TSingleBit;
    use Opcode\TArithmetic;
    use Opcode\TShifter;
    use Opcode\TFlow;
    use Opcode\TSpecial;

    public function __construct(Device\IBus $oOutside, bool $bCache = false)
    {
        $this->oOutside  = $oOutside;
        $this->initRegIndexes();
        $this->initEAModes();

        // Caching affects how some handlers are generated.
        if ($bCache) {
            echo "Enable Caches\n";
            $this->enableJumpCache();
            $this->enableImmediateCache();
        }

        $iStartMem = memory_get_usage();
        $fMark = microtime(true);

        // Install our opcode handlers.
        $this->clearCompilerCache();
        $this->initMoveHandlers();
        $this->initLogicalHandlers();
        $this->initSingleBitHandlers();
        $this->initArithmeticHandlers();
        $this->initShifterHandlers();
        $this->initFlowHandlers();
        $this->initSpecialHandlers();
        $this->clearCompilerCache();

        $fElapsed = microtime(true) - $fMark;
        $iUsedMem = memory_get_usage() - $iStartMem;

        echo "Handler setup took ", $fElapsed, " seconds using ", $iUsedMem, " bytes\n";

        $this->reportHandlerStats();
    }

    public function softReset(): self
    {
        $this->registerReset();
        $this->oOutside->softReset();
        return $this;
    }

    public function hardReset(): self
    {
        $this->registerReset();
        $this->oOutside->hardReset();
        return $this;
    }

    /**
     * Helper function for helping transitioning into supervisor state.
     *
     * If we are in the user state, current a7 is saved to the usp and is then reloaded from
     * the ssp. Otherwise, current a7 is synced to the ssp.
     *
     */
    protected function syncSupervisorState()
    {
        if ($this->iStatusRegister & IRegister::SR_MASK_SUPER) {
            $this->iSupervisorStackPtrRegister = $this->oAddressRegisters->iReg7;
        } else {
            $this->iUserStackPtrRegister = $this->oAddressRegisters->iReg7;
            $this->oAddressRegisters->iReg7 = $this->iSupervisorStackPtrRegister;
            $this->iStatusRegister |= IRegister::SR_MASK_SUPER;
        }
    }

    /**
     * Helper function for helping transitioning into user state.
     *
     * If we are in the supervisor state, current a7 is saved to the ssp and is then reloaded from
     * the usp. Otherwise, current a7 is synced to the usp.     *
     */
    protected function syncUserState()
    {
        if ($this->iStatusRegister & IRegister::SR_MASK_SUPER) {
            $this->iSupervisorStackPtrRegister = $this->oAddressRegisters->iReg7;
            $this->oAddressRegisters->iReg7 = $this->iUserStackPtrRegister;
            $this->iStatusRegister &= ~IRegister::SR_MASK_SUPER;
        } else {
            $this->iUserStackPtrRegister = $this->oAddressRegisters->iReg7;
        }
    }

    protected function syncFromStackPointer()
    {
        if ($this->iStatusRegister & IRegister::SR_MASK_SUPER) {
            $this->iSupervisorStackPtrRegister = $this->oAddressRegisters->iReg7;
        } else {
            $this->iUserStackPtrRegister = $this->oAddressRegisters->iReg7;
        }
    }
}
