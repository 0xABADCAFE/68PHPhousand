#!/bin/sh

php -dopcache.enable_cli=0 bench_mem.php
php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=2M -dopcache.jit=1255 bench_mem.php
php -dopcache.enable_cli=0 bench_rom.php
php -dopcache.enable_cli=1 -dopcache.jit_buffer_size=2M -dopcache.jit=1255 bench_rom.php
