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

namespace ABadCafe\G8PHPhousand\TestHarness;

use ABadCafe\G8PHPhousand\Device;

class ObjectCode
{
    public array  $aSourceMap = [];
    public string $sCode;
    public int    $iBaseAddress;

    public function __construct(string $sSource, string $sCode, int $iBaseAddress)
    {
        $this->sCode = $sCode;
        $this->iBaseAddress = $iBaseAddress;
        $this->createSourceMap($sSource);
    }

    private function createSourceMap(string $sSource)
    {
        preg_match_all(
            '/^[0-9A-F]{2}:([0-9A-F]{8})\s+([0-9A-F]{2,})\s+(\d+):(.*?)$/m',
            $sSource,
            $aMatches
        );

        $this->aSourceMap = [];
        foreach (array_keys($aMatches[0]) as $iIndex) {
            $iAddress = (int)base_convert($aMatches[1][$iIndex], 16, 10);
            $this->aSourceMap[$iAddress] = (object)[
                'sOpcode'  => $aMatches[2][$iIndex],
                'iLineNum' => $aMatches[3][$iIndex],
                'sLineSrc' => $aMatches[4][$iIndex]
            ];
        }
    }
}


