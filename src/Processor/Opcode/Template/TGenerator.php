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


trait TGenerator
{
    /** @var array<string, callable> */
    protected array $aCompilerCache = [];


    protected function clearCompilerCache(): void
    {
        $this->aCompilerCache = [];
    }

    /**
     * Compiles a handler from a template.
     */
    protected function compileTemplateHandler(Params $oParams): callable
    {
        ob_start();
        include($oParams->sPath);
        $sCode = ob_get_clean();
        $sHash = sha1($sCode);

        printf("\n%s()\n$%4X : %s => %s\n", __METHOD__, $oParams->iOpcode, $sHash, $sCode);

        return $this->aCompilerCache[$sHash] ?? ($this->aCompilerCache[$sHash] = eval(
            "namespace " . Params::EXECUTION_NAMESPACE . ";\n" . $sCode)
        );
    }


}
