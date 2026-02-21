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

namespace ABadCafe\G8PHPhousand\Device;

use ABadCafe\G8PHPhousand\IDevice;
use ABadCafe\G8PHPhousand\Processor\ISize;
use ABadCafe\G8PHPhousand\Processor\Fault;

use \LogicException;

/**
 * Union interface for read/write
 */
final class PagedMap implements IBusAccessible
{
    private int $iPageSizeExp;
    private int $iPageMask;
    private int $iPageSize;

    private array $aDeviceMap = [];
    private array $aDevices = [];

    private Fault\Access $oFault;

    public const MIN_SIZE_EXP = 8;
    public const MAX_SIZE_EXP = 20;
    public const DEF_SIZE_EXP = 16;

    public function __construct(int $iPageSizeExp = self::DEF_SIZE_EXP)
    {
        assert(
            $iPageSizeExp >= self::MIN_SIZE_EXP &&
            $iPageSizeExp <= self::MAX_SIZE_EXP,
            new LogicException()
        );
        $this->iPageSizeExp = $iPageSizeExp;
        $this->iPageSize = 1 << $iPageSizeExp;
        $this->iPageMask = ~($this->iPageSize - 1) & ISize::MASK_LONG;
        $this->oFault = new Fault\Access();
    }

    public function add(IAddressMapped $oDevice): void
    {
        $this->map(
            $oDevice,
            $oDevice->getBaseAddress(),
            $oDevice->getLength()
        );
    }

    public function map(IBusAccessible $oDevice, int $iBaseAddress, int $iLength): void
    {
        assert(
            fprintf(
                STDERR,
                "PagedMap: Adding %s at 0x%08X [%d size]\n",
                $oDevice->getName(),
                $iBaseAddress,
                $iLength
            ) || true
        );

        $this->aDevices[spl_object_id($oDevice)] = $oDevice;

        $iPages = ($iLength >> $this->iPageSizeExp) + ($iLength <= $this->iPageMask ? 1 : 0);
        $iPage  = $iBaseAddress & $this->iPageMask;
        while ($iPages--) {
            if (isset($this->aDeviceMap[$iPage])) {
                throw new \LogicException(sprintf(
                    'Cannot assign device %s to page 0x%08X, already allocated to %s',
                    $oDevice->getName(),
                    $iPage,
                    $this->aDeviceMap[$iPage]->getName()
                ));
            }
            $this->aDeviceMap[$iPage] = $oDevice;
            $iPage += $this->iPageSize;
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::class;
    }

    public function softReset(): self
    {
        foreach ($this->aDevices as $oDevice) {
            $oDevice->softReset();
        }
        return $this;
    }

    public function hardReset(): self
    {
        foreach ($this->aDevices as $oDevice) {
            $oDevice->hardReset();
        }
        return $this;
    }

    public function readByte(int $iAddress): int
    {
        return (
            $this->aDeviceMap[$iAddress & $this->iPageMask] ??
            $this->oFault->raise($iAddress, ISize::BYTE, false)
        )->readByte($iAddress);
    }

    public function readWord(int $iAddress): int
    {
        return (
            $this->aDeviceMap[$iAddress & $this->iPageMask] ??
            $this->oFault->raise($iAddress, ISize::WORD, false)
        )->readWord($iAddress);
    }

    public function readLong(int $iAddress): int
    {
        return (
            $this->aDeviceMap[$iAddress & $this->iPageMask] ??
            $this->oFault->raise($iAddress, ISize::LONG, false)
        )->readLong($iAddress);
    }

    public function writeByte(int $iAddress, int $iValue): void
    {
        (
            $this->aDeviceMap[$iAddress & $this->iPageMask]
            ?? $this->oFault->raise($iAddress, ISize::BYTE, true)
        )->writeByte($iAddress, $iValue);
    }

    public function writeWord(int $iAddress, int $iValue): void
    {
        (
            $this->aDeviceMap[$iAddress & $this->iPageMask] ??
            $this->oFault->raise($iAddress, ISize::WORD, true)
        )->writeWord($iAddress, $iValue);
    }

    public function writeLong(int $iAddress, int $iValue): void
    {
        (
            $this->aDeviceMap[$iAddress & $this->iPageMask] ??
            $this->oFault->raise($iAddress, ISize::LONG, true)
        )->writeLong($iAddress, $iValue);
    }
}


