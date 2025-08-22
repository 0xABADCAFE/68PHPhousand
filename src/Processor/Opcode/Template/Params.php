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

namespace ABadCafe\G8PHPhousand\Processor\Opcode\Template;
use ABadCafe\G8PHPhousand;
use \DomainException;
use \LogicException;
use \stdClass;

/**
 * Simple parameter class for the code generator. This contains the path to the template, the opcode number the
 * genration is for and any additional parameters needed to fill in the template
 */
class Params
{
    // If making PHP8 required, make these readonly
    public int      $iOpcode;
    public string   $sName;
    public string   $sPath;
    public string   $sBasePath;
    public stdClass $oAdditional;

    public function __construct(int $iOpcode, string $sName, array $aAdditional = [])
    {
        assert(
            $iOpcode >= 0 && $iOpcode <= 0xFFFF,
            new DomainException('Invalid opcode number #'. $iOpcode)
        );
        assert(!empty($sName), new LogicException('Empty template path'));
        $this->sBasePath = G8PHPhousand\PROJ_SRC_BASE . '/templates';
        $sPath = sprintf(
            '%s/%s.tpl.php',
            $this->sBasePath,
            $sName
        );
        assert(file_exists($sPath), new LogicException('No template ' . $sPath));
        $this->iOpcode     = $iOpcode;
        $this->sName       = $sName;
        $this->sPath       = $sPath;
        $this->oAdditional = (object)$aAdditional;
    }
}
