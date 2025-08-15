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

namespace ABadCafe\G8PHPhousand\Processor\EAMode;

interface IReadWrite extends IReadOnly
{

    /**
     * By default, writeable EA will be used in a destination read/modify/write context. Where there are side effects
     * of determining the address, we will latch the calculated address in the read cycle, to avoid repeating it
     * during the write cycle and getting the wrong address. Calling noLatch after a read will clear the latched
     * address, forcing the address to be calculated again.
     *
     * The latch will always be cleared by a write.
     */
    public function resetLatch(): void;

    /**
     * @param int<0,255> $iValue
     */
    public function writeByte(int $iValue): void;

    /**
     * @param int<0,65535> $iValue
     */
    public function writeWord(int $iValue): void;

    /**
     * @param int<0,4294967295> $iValue
     */
    public function writeLong(int $iValue): void;

}
