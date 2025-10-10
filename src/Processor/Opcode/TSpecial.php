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
use ABadCafe\G8PHPhousand\Processor\IOpcode;
use ABadCafe\G8PHPhousand\Processor\IRegister;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\IEffectiveAddress;
use ABadCafe\G8PHPhousand\Processor\Sign;

use LogicException;

trait TSpecial
{
    use Processor\TOpcode;

    protected function initSpecialHandlers()
    {
        $cUnhandled = function(int $iOpcode) {
            throw new LogicException(sprintf('Unhandled special operation 0x%4X (TODO)', $iOpcode));
        };

        $this->addExactHandlers([
            ISpecial::OP_ILLEGAL  => $cUnhandled,

            ISpecial::OP_RESET    => function() {
                // TODO - probably needs to be a bit more specific than this
                $this->reset();
            },

            ISpecial::OP_NOP      => function() {
                // Nothing yet
            },

        ]);

        // TAS
        $this->addExactHandlers(
            array_fill_keys(
                $this->generateForEAModeList(
                    IEffectiveAddress::MODE_DATA_ALTERABLE,
                    ISpecial::OP_TAS
                ),
                function (int $iOpcode) {
                    $oEAMode = $this->aDstEAModes[$iOpcode & IOpcode::MASK_OP_STD_EA];
                    $this->iConditionRegister &= IRegister::CCR_CLEAR_CV;
                    $iByte = $oEAMode->readByte();
                    $this->updateNZByte($iByte);
                    $oEAMode->writeByte($iByte | ISize::SIGN_BIT_BYTE);
                }
            )
        );

        // LINK
        $this->addExactHandlers(
            array_fill_keys(
                range(
                    ISpecial::OP_LINK|IRegister::A0,
                    ISpecial::OP_LINK|Iregister::A7
                ),
                function (int $iOpcode) {
                    $iSP  = &$this->oAddressRegisters->iReg7;
                    $iSP -= ISize::LONG;
                    $iSP &= ISize::MASK_LONG;
                    $iReg = &$this->oAddressRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG];
                    $this->oOutside->writeLong($iSP, $iReg);
                    $iReg = $iSP;
                    $iSP  += Sign::extWord($this->oOutside->readWord($this->iProgramCounter));
                    $iSP &= ISize::MASK_LONG;
                    $this->iProgramCounter += ISize::WORD;
                    $this->iProgramCounter &= ISize::MASK_LONG;
                }
            )
        );


        // UNLK
        $this->addExactHandlers(
            array_fill_keys(
                range(
                    ISpecial::OP_UNLK|IRegister::A0,
                    ISpecial::OP_UNLK|Iregister::A7
                ),
                function (int $iOpcode) {
                    $iSP  = &$this->oAddressRegisters->iReg7;
                    $iReg = &$this->oAddressRegisters->aIndex[$iOpcode & IOpcode::MASK_EA_REG];
                    $bPop = $iSP !== $iReg;
                    $iSP  = $iReg;
                    $iReg = $this->oOutside->readLong($iSP);
                    if ($bPop) {
                        $iSP += ISize::LONG;
                        $iSP &= ISize::MASK_LONG;
                    }
                }
            )
        );
    }
}
