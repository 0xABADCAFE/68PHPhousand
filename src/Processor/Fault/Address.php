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

namespace ABadCafe\G8PHPhousand\Processor\Fault;

use Exception;

/**
 * This exception type is NOT intended for debugging, but rather as a mechanisn to abort the
 * regular fetch-execute cycle and put the CPU into an exception handling case.
 */
class Address extends Exception
{
    public int  $iAddress = 0;
    public int  $iSize    = 0;
    public bool $bWrite   = false;

    /**
     * Raise this fault.
     */
    public function raise(int $iAddress, int $iSize, bool $bWrite): bool
    {
        $this->iAddress = $iAddress;
        $this->iSize    = $iSize;
        $this->bWrite   = $bWrite;
        throw $this;
        return false;
    }
}

