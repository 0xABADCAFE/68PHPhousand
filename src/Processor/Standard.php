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
class Standard extends Base
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
        return 'Standard 68K';
    }

    public function execute(): int
    {
        $iCount = 0;
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
        catch (LogicException $oError) {

        }
        return $iCount;
    }
}
