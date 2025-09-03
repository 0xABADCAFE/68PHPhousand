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

/**
 * Trait for opcode handler
 */
trait TCache
{
    /** @var array<int, int> */
    protected ?array $aJumpCache = null;

    /** @var array<int, int> */
    protected ?array $aImmediateCache = null;

    protected function enableJumpCache()
    {
        $this->aJumpCache = [];
    }

    protected function enableImmediateCache()
    {
        $this->aImmediateCache = [];
    }

    protected function jumpCacheEnabled(): bool
    {
        return null !== $this->aJumpCache;
    }

    protected function immediateCacheEnabled(): bool
    {
        return null !== $this->aImmediateCache;
    }
}
