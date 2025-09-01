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
    use Opcode\Template\TGenerator;
    use Opcode\TMove;
    use Opcode\TLogical;
    use Opcode\TSingleBit;
    use Opcode\TArithmetic;
    use Opcode\TFlow;
    use Opcode\TConditional;
    use Opcode\TSpecial;

    public function __construct(Device\IBus $oOutside)
    {
        $this->oOutside  = $oOutside;
        $this->initRegIndexes();
        $this->initEAModes();

        // Install our opcode handlers.
        $this->clearCompilerCache();
        $this->initMoveHandlers();
        $this->initLogicalHandlers();
        $this->initSingleBitHandlers();
        $this->initArithmeticHandlers();
        $this->initFlowHandlers();
        $this->initConditionalHandlers();
        $this->initSpecialHandlers();
        $this->clearCompilerCache();
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

    protected function execute()
    {
        $iOpcode =  $this->oOutside->readWord($this->iProgramCounter);
        $this->iProgramCounter += ISize::WORD;
    }
}
