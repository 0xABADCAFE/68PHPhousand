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

use LogicException;

/**
 * Trait for opcode handler
 */
trait TOpcode
{
    use TRegisterUnit;
    use TArithmeticLogicUnit;

    /** @var array<int, callable> */
    protected array $aExactHandler = [];

    /** @var array<int, callable> */
    protected array $aPrefixHandler = [];

    /**
     * Populates the aExactHandler array with callables for each of the opcode bit patterns
     * that are unique, i.e. all bits encode only the operation and not any parameters.
     */

    protected function addExactHandlers(array $aHandlers)
    {
        foreach($aHandlers as $iPrefix => $cHandler) {
            $this->aExactHandler[$iPrefix] = $cHandler;
        }
    }

    protected function addPrefixHandlers(array $aHandlers)
    {
        foreach($aHandlers as $iPrefix => $cHandler) {
            $this->aPrefixHandler[$iPrefix] = $cHandler;
        }
    }

}
