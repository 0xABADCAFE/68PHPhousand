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

namespace ABadCafe\G8PHPhousand\TestHarness\Assembler;

use ABadCafe\G8PHPhousand\TestHarness\IAssembler;
use ABadCafe\G8PHPhousand\Device;

use RuntimeExcepction;

/**
 * Simple implementaiton of IAssembler that wraps around an existing installation of the
 * VASM cross-assembler for 68K.
 */
class Vasmm68k implements IAssembler
{
    const BIN_NAME = 'vasmm68k_mot';

    /**
     * Installed location of the vasm binary
     */
    private string $sBinPath;

    /**
     * System defined temporary directory
     */
    private string $sTmpDir;

    public function __construct()
    {
        $this->sTmpDir = sys_get_temp_dir();
        $sPath   = exec(sprintf('locate %s', self::BIN_NAME));
        if (empty($sPath) || !is_executable($sPath)) {
            throw new RuntimeException(sprintf('Unable to locate %s executable', self::BIN_NAME));
        }
        $this->sBinPath = $sPath;
    }

    public function assemble(string $sSourceCode): Device\IBus
    {
        $sSource = tempnam($this->sTmpDir, 'src_');
        $sTarget = tempnam($this->sTmpDir, 'bin_');

        try {
            file_put_contents($sSource, $sSourceCode);
            $sCommand = sprintf(
                "%s %s -Fbin -o %s\n",
                $this->sBinPath,
                $sSource,
                $sTarget
            );
            $sResult = exec($sCommand);
            if (empty($sResult)) {
                throw new RuntimeException('Failed to assemble source');
            }
            return new Device\CodeROM($sTarget);
        } finally {
            unlink($sSource);
            unlink($sTarget);
        }
    }

}



