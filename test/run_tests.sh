#!/bin/sh

php -dzend.assertions=1 test_memory.php
php -dzend.assertions=1 test_regs.php
