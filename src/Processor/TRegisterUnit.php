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
use ValueError;

/**
 */
trait TRegisterUnit
{
    protected int $iProgramCounter    = 0;
    protected int $iStatusRegister    = 0;
    protected int $iConditionRegister = 0;

    protected AddressRegisterSet $oAddressRegisters;
    protected DataRegisterSet $oDataRegisters;

    protected array $aRegisterNames = [];

    // Processor model and address mask
    protected int $iProcessorModel = IProcessorModel::MC68000;
    protected int $iAddressMask = 0x00FFFFFF; // Default to 68000's 24-bit

    // Control registers (68010+)
    protected int $iVectorBaseRegister = 0;        // VBR (68010+)
    protected int $iSourceFunctionCode = 0;        // SFC (68010+, 3 bits)
    protected int $iDestinationFunctionCode = 0;   // DFC (68010+, 3 bits)

    // Control registers (68020+)
    protected int $iCacheControlRegister = 0;      // CACR (68020+)
    protected int $iCacheAddressRegister = 0;      // CAAR (68020+)
    protected int $iMasterStackPointer = 0;        // MSP (68020+)
    protected int $iInterruptStackPointer = 0;     // ISP (68020+)

    public function getPC(): int
    {
        return $this->iProgramCounter;
    }

    public function setPC(int $iAddress): self
    {
        assert(0 === ($iAddress & 1), new LogicException('Misaligned PC'));
        $this->iProgramCounter = $iAddress & $this->iAddressMask;
        return $this;
    }

    public function getModel(): int
    {
        return $this->iProcessorModel;
    }

    public function getModelName(): string
    {
        return IProcessorModel::NAMES[$this->iProcessorModel];
    }

    /**
     * Get control register value
     * @throws LogicException if register not supported on current processor model
     */
    public function getControlRegister(int $iRegCode): int
    {
        // Check if register is supported on this processor model
        if (isset(IControlRegister::MIN_MODEL[$iRegCode])) {
            assert(
                $this->iProcessorModel >= IControlRegister::MIN_MODEL[$iRegCode],
                new LogicException(
                    sprintf(
                        'Control register %s not supported on %s',
                        IControlRegister::NAMES[$iRegCode] ?? sprintf('$%03X', $iRegCode),
                        $this->getModelName()
                    )
                )
            );
        }

        return match($iRegCode) {
            IControlRegister::SFC  => $this->iSourceFunctionCode,
            IControlRegister::DFC  => $this->iDestinationFunctionCode,
            IControlRegister::CACR => $this->iCacheControlRegister,
            IControlRegister::USP  => $this->oAddressRegisters->iReg7, // A7 in user mode
            IControlRegister::VBR  => $this->iVectorBaseRegister,
            IControlRegister::CAAR => $this->iCacheAddressRegister,
            IControlRegister::MSP  => $this->iMasterStackPointer,
            IControlRegister::ISP  => $this->iInterruptStackPointer,
            default => throw new LogicException(sprintf('Unknown control register $%03X', $iRegCode))
        };
    }

    /**
     * Set control register value
     * @throws LogicException if register not supported on current processor model
     */
    public function setControlRegister(int $iRegCode, int $iValue): self
    {
        // Check if register is supported on this processor model
        if (isset(IControlRegister::MIN_MODEL[$iRegCode])) {
            assert(
                $this->iProcessorModel >= IControlRegister::MIN_MODEL[$iRegCode],
                new LogicException(
                    sprintf(
                        'Control register %s not supported on %s',
                        IControlRegister::NAMES[$iRegCode] ?? sprintf('$%03X', $iRegCode),
                        $this->getModelName()
                    )
                )
            );
        }

        switch($iRegCode) {
            case IControlRegister::SFC:
                $this->iSourceFunctionCode = $iValue & 0x7; // 3 bits
                break;
            case IControlRegister::DFC:
                $this->iDestinationFunctionCode = $iValue & 0x7; // 3 bits
                break;
            case IControlRegister::CACR:
                $this->iCacheControlRegister = $iValue & 0xFFFF; // 16 bits on 68020
                break;
            case IControlRegister::USP:
                $this->oAddressRegisters->iReg7 = $iValue & 0xFFFFFFFF;
                break;
            case IControlRegister::VBR:
                $this->iVectorBaseRegister = $iValue & 0xFFFFFFFF;
                break;
            case IControlRegister::CAAR:
                $this->iCacheAddressRegister = $iValue & 0xFFFFFFFF;
                break;
            case IControlRegister::MSP:
                $this->iMasterStackPointer = $iValue & 0xFFFFFFFF;
                break;
            case IControlRegister::ISP:
                $this->iInterruptStackPointer = $iValue & 0xFFFFFFFF;
                break;
            default:
                throw new LogicException(sprintf('Unknown control register $%03X', $iRegCode));
        }
        return $this;
    }

    public function getRegister(string $sRegName): int
    {
        assert(
            isset($this->aRegisterNames[$sRegName]),
            new ValueError('Illegal register name ' . $sRegName)
        );
        return $this->aRegisterNames[$sRegName];
    }

    public function setRegister(string $sRegName, int $iValue): self
    {
        assert(
            isset($this->aRegisterNames[$sRegName]),
            new ValueError('Illegal register name ' . $sRegName)
        );
        $this->aRegisterNames[$sRegName] = $iValue & 0xFFFFFFFF;
        return $this;
    }

    protected function initRegIndexes(): void
    {
        $this->oAddressRegisters = new AddressRegisterSet();
        $this->oDataRegisters    = new DataRegisterSet();
        $this->aRegisterNames[IRegister::PC_NAME]  = &$this->iProgramCounter;
        $this->aRegisterNames[IRegister::SR_NAME]  = &$this->iStatusRegister;
        $this->aRegisterNames[IRegister::CCR_NAME] = &$this->iConditionRegister;
        foreach (IRegister::ADDR_NAMES as $iIndex => $sName) {
            $this->aRegisterNames[$sName] = &$this->oAddressRegisters->aIndex[$iIndex];
        }
        foreach (IRegister::DATA_NAMES as $iIndex => $sName) {
            $this->aRegisterNames[$sName] = &$this->oDataRegisters->aIndex[$iIndex];
        }
    }

    protected function registerReset(): void
    {
        $this->iProgramCounter = 0;
        $this->iStatusRegister = 0;
        $this->iConditionRegister = 0;
        $this->oAddressRegisters->reset();
        $this->oDataRegisters->reset();

        // Reset control registers (68010+)
        $this->iVectorBaseRegister = 0;
        $this->iSourceFunctionCode = 0;
        $this->iDestinationFunctionCode = 0;

        // Reset control registers (68020+)
        $this->iCacheControlRegister = 0;
        $this->iCacheAddressRegister = 0;
        $this->iMasterStackPointer = 0;
        $this->iInterruptStackPointer = 0;
    }
}
