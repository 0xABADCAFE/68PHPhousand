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

use ABadCafe\G8PHPhousand;

use LogicException;

use \stdClass;

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

            printf("Adding Handler for Opcode $%04X\n", $iPrefix);

            assert(
                !isset($this->aExactHandler[$iPrefix]),
                new LogicException(sprintf("Duplicate handler $%04X", $iPrefix))
            );
            $this->aExactHandler[$iPrefix] = $cHandler;
        }
    }

    protected function addPrefixHandlers(array $aHandlers)
    {
        foreach($aHandlers as $iPrefix => $cHandler) {
            $this->aPrefixHandler[$iPrefix] = $cHandler;
        }
    }

    protected function reportHandlerStats()
    {
        printf(
            "%d Exact and %d Prefix handlers defined\n",
            count($this->aExactHandler),
            count($this->aPrefixHandler)
        );
    }

    /**
     *  @param array<int> $aGroups
     *  @return array<int>
     */
    protected function generateForEAModeList(
        array $aModes,
        int   $iOpcode    = 0,
        int   $iModeShift = 3,
        int   $iRegShift  = 0
    ): array {
        $aMerge   = [];
        foreach ($aModes as $iMode => $aRecords) {
            foreach ($aRecords as &$iRecord) {
                $iRecord = $iOpcode|(($iMode & 7) << $iModeShift)|(($iRecord & 7) << $iRegShift);
            }
            $aMerge[] = $aRecords;
        }
        return array_merge(...$aMerge);
    }

    protected function mergePrefixForModeList(int $iPrefix, array $aEAModes): array
    {
        foreach ($aEAModes as &$iMode) {
            $iMode |= $iPrefix;
        }
        return $aEAModes;
    }
}
