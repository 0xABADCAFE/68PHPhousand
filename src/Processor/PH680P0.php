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
 * Standard implementation
 */
class PH680P0 extends Base
{
    public function softReset(): self
    {
        parent::softReset();
        $this->execute();
        return $this;
    }

    public function hardReset(): self
    {
        parent::hardReset();
        $this->execute();
        return $this;
    }

    public function getName(): string
    {
        return 'PH680P0';
    }

    public function execute(): int
    {
        $iCount = 0;
        try {
            while (true) {
                try {
                    while(true) {
                        $iOpcode = $this->oOutside->readWord($this->iProgramCounter);
                        $this->iProgramCounter += Processor\ISize::WORD;
                        $this->aExactHandler[$iOpcode]($iOpcode);
                        ++$iCount;
                    };
                }
                catch (Fault\Access $oFault) {
                    $this->processAccessError(
                        $oFault,
                        $this->iProgramCounter - Processor\ISize::WORD,
                        $iOpcode
                    );
                }
                catch (Fault\Address $oFault) {
                    $this->processAddressError(
                        $oFault,
                        $this->iProgramCounter - Processor\ISize::WORD,
                        $iOpcode
                    );
                }
                catch (\DivisionByZeroError $oFault) {
                    $this->processZeroDivideError();
                }
            }
        }
        catch (Halted $oHalt) {
            assert(
                fprintf(
                    STDERR,
                    "CPU: Emulation halted by STOP #%d at 0x%08X\n",
                    $oHalt->iImmediate,
                    $this->iProgramCounter - Processor\ISize::WORD
                ) || true
            );
        }
        catch (LogicException $oError) {
            echo "Emulation terminated\n";
        }
        return $iCount;
    }
}
