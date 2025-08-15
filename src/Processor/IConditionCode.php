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
 * Basic CC enumerations
 */
interface IConditionCode
{
    const T  =  0; // True
    const F  =  1; // False
    const HI =  2; // High
    const LS =  3; // Low or Same
    const CC =  4; // Carry Clear
    const CS =  5; // Carry Set
    const NE =  6; // Not Equal
    const EQ =  7; // Equal
    const VC =  8; // Overflow Clear
    const VS =  9; // Overflow Set
    const PL = 10; // Plus
    const MI = 11; // Minus
    const GE = 12; // Greater or Equal
    const LT = 13; // Less Than
    const GT = 14; // Greater Than
    const LE = 15; // Less or Equal
}

