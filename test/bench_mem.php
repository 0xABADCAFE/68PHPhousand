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

namespace ABadCafe\G8PHPhousand;

use LogicException;

error_reporting(-1);
require  __DIR__ . '/../src/bootstrap.php';

const MEM_SIZE = 1 << 24; // 16MiB

const MB_SCALE = 1.0 / (1 << 20);


function benchmark(Device\IBusAccessible $oMemory) {

    echo "Benchmarking ", $oMemory->getName(), "\n";

    $fTime = microtime(true);
    for ($i = 0; $i < MEM_SIZE; $i += 16) {
        $oMemory->writeByte($i + 0, 1);
        $oMemory->writeByte($i + 1, 2);
        $oMemory->writeByte($i + 2, 3);
        $oMemory->writeByte($i + 3, 4);
        $oMemory->writeByte($i + 4, 5);
        $oMemory->writeByte($i + 5, 6);
        $oMemory->writeByte($i + 6, 7);
        $oMemory->writeByte($i + 7, 8);
        $oMemory->writeByte($i + 8, 9);
        $oMemory->writeByte($i + 9, 10);
        $oMemory->writeByte($i + 10, 11);
        $oMemory->writeByte($i + 11, 12);
        $oMemory->writeByte($i + 12, 13);
        $oMemory->writeByte($i + 13, 14);
        $oMemory->writeByte($i + 14, 15);
        $oMemory->writeByte($i + 15, 16);
    }
    $fTime = microtime(true) - $fTime;
    printf("writeByte() %d bytes in %.6f seconds, %.3f MiB/sec\n", MEM_SIZE, $fTime, MB_SCALE * MEM_SIZE / $fTime);

    $fTime = microtime(true);
    for ($i = 0; $i < MEM_SIZE; $i += 16) {
        $oMemory->readByte($i + 0);
        $oMemory->readByte($i + 1);
        $oMemory->readByte($i + 2);
        $oMemory->readByte($i + 3);
        $oMemory->readByte($i + 4);
        $oMemory->readByte($i + 5);
        $oMemory->readByte($i + 6);
        $oMemory->readByte($i + 7);
        $oMemory->readByte($i + 8);
        $oMemory->readByte($i + 9);
        $oMemory->readByte($i + 10);
        $oMemory->readByte($i + 11);
        $oMemory->readByte($i + 12);
        $oMemory->readByte($i + 13);
        $oMemory->readByte($i + 14);
        $oMemory->readByte($i + 15);
    }
    $fTime = microtime(true) - $fTime;
    printf("readByte()  %d bytes in %.6f seconds, %.3f MiB/sec\n", MEM_SIZE, $fTime, MB_SCALE * MEM_SIZE / $fTime);


    $fTime = microtime(true);
    for ($i = 0; $i < MEM_SIZE; $i += 32) {
        $oMemory->writeWord($i + 0, 1);
        $oMemory->writeWord($i + 2, 2);
        $oMemory->writeWord($i + 4, 3);
        $oMemory->writeWord($i + 6, 4);
        $oMemory->writeWord($i + 8, 5);
        $oMemory->writeWord($i + 10, 6);
        $oMemory->writeWord($i + 12, 7);
        $oMemory->writeWord($i + 14, 8);
        $oMemory->writeWord($i + 16, 9);
        $oMemory->writeWord($i + 18, 10);
        $oMemory->writeWord($i + 20, 11);
        $oMemory->writeWord($i + 22, 12);
        $oMemory->writeWord($i + 24, 13);
        $oMemory->writeWord($i + 26, 14);
        $oMemory->writeWord($i + 28, 15);
        $oMemory->writeWord($i + 30, 16);
    }
    $fTime = microtime(true) - $fTime;
    printf("writeWord() %d bytes in %.6f seconds, %.3f MiB/sec\n", MEM_SIZE, $fTime, MB_SCALE * MEM_SIZE / $fTime);

    $fTime = microtime(true);
    for ($i = 0; $i < MEM_SIZE; $i += 32) {
        $oMemory->readWord($i + 0);
        $oMemory->readWord($i + 2);
        $oMemory->readWord($i + 4);
        $oMemory->readWord($i + 6);
        $oMemory->readWord($i + 8);
        $oMemory->readWord($i + 10);
        $oMemory->readWord($i + 12);
        $oMemory->readWord($i + 14);
        $oMemory->readWord($i + 16);
        $oMemory->readWord($i + 18);
        $oMemory->readWord($i + 20);
        $oMemory->readWord($i + 22);
        $oMemory->readWord($i + 24);
        $oMemory->readWord($i + 26);
        $oMemory->readWord($i + 28);
        $oMemory->readWord($i + 30);
    }
    $fTime = microtime(true) - $fTime;
    printf("readWord()  %d bytes in %.6f seconds, %.3f MiB/sec\n", MEM_SIZE, $fTime, MB_SCALE * MEM_SIZE / $fTime);

    $fTime = microtime(true);
    for ($i = 0; $i < MEM_SIZE; $i += 64) {
        $oMemory->writeLong($i + 0, 1);
        $oMemory->writeLong($i + 4, 2);
        $oMemory->writeLong($i + 8, 3);
        $oMemory->writeLong($i + 12, 4);
        $oMemory->writeLong($i + 16, 5);
        $oMemory->writeLong($i + 20, 6);
        $oMemory->writeLong($i + 24, 7);
        $oMemory->writeLong($i + 28, 8);
        $oMemory->writeLong($i + 32, 9);
        $oMemory->writeLong($i + 36, 10);
        $oMemory->writeLong($i + 40, 11);
        $oMemory->writeLong($i + 44, 12);
        $oMemory->writeLong($i + 48, 13);
        $oMemory->writeLong($i + 52, 14);
        $oMemory->writeLong($i + 56, 15);
        $oMemory->writeLong($i + 60, 16);
    }
    $fTime = microtime(true) - $fTime;
    printf("writeLong() %d bytes in %.6f seconds, %.3f MiB/sec\n", MEM_SIZE, $fTime, MB_SCALE * MEM_SIZE / $fTime);

    $fTime = microtime(true);
    for ($i = 0; $i < MEM_SIZE; $i += 64) {
        $oMemory->readLong($i + 0);
        $oMemory->readLong($i + 4);
        $oMemory->readLong($i + 8);
        $oMemory->readLong($i + 12);
        $oMemory->readLong($i + 16);
        $oMemory->readLong($i + 20);
        $oMemory->readLong($i + 24);
        $oMemory->readLong($i + 28);
        $oMemory->readLong($i + 32);
        $oMemory->readLong($i + 36);
        $oMemory->readLong($i + 40);
        $oMemory->readLong($i + 44);
        $oMemory->readLong($i + 48);
        $oMemory->readLong($i + 52);
        $oMemory->readLong($i + 56);
        $oMemory->readLong($i + 60);
    }
    $fTime = microtime(true) - $fTime;
    printf("readLong()  %d bytes in %.6f seconds, %.3f MiB/sec\n", MEM_SIZE, $fTime, MB_SCALE * MEM_SIZE / $fTime);
}

echo "Memory Benchmark\n";

$aOpcacheStatus = opcache_get_status();
if (isset($aOpcacheStatus['jit'])) {
    echo "JIT parameters: ";
    print_r($aOpcacheStatus['jit']);
} else {
    echo "JIT mode disabled\n";
}

benchmark(new Device\Memory\BinaryRAM(MEM_SIZE, 0));
benchmark(new Device\Memory\SparseRAM());
benchmark(new Device\Memory\SparseWordRAM());

echo "With DeviceMap\n";

