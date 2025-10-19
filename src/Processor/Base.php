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

    use Opcode\Template\TGenerator;
    use Opcode\TMove;
    use Opcode\TLogical;
    use Opcode\TSingleBit;
    use Opcode\TArithmetic;
    use Opcode\TShifter;
    use Opcode\TFlow;
    use Opcode\TSpecial;
    use Opcode\TControlRegister;
    use Opcode\TAtomic;
    use Opcode\TCoprocessor;

    public function __construct(
        Device\IBus $oOutside,
        bool $bCache = false,
        int $iProcessorModel = IProcessorModel::MC68000
    ) {
        $this->oOutside  = $oOutside;
        $this->iProcessorModel = $iProcessorModel;
        $this->iAddressMask = IProcessorModel::ADDRESS_MASK[$iProcessorModel];

        echo "Initializing ", IProcessorModel::NAMES[$iProcessorModel], " emulator\n";

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

        // Install 68010+ handlers if applicable
        if ($iProcessorModel >= IProcessorModel::MC68010) {
            $this->initControlRegisterHandlers();
        }

        // Install 68020+ handlers if applicable
        if ($iProcessorModel >= IProcessorModel::MC68020) {
            $this->initAtomicHandlers();
            $this->initCoprocessorHandlers();
        }

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


}
