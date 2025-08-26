<?php

declare(strict_types=1);

/**
 * This file is part of the Phalcon API.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Phalcon\Api\Tests\Fixtures\Domain;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function base64_encode;
use function file_exists;
use function file_get_contents;
use function gc_collect_cycles;
use function is_dir;
use function is_file;
use function random_bytes;
use function rmdir;
use function rtrim;
use function substr;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

abstract class AbstractUnitTestCase extends TestCase
{
    /**
     * @param string $fileName
     * @param string $stream
     *
     * @return void
     */
    public function assertFileContentsContains(string $fileName, string $stream): void
    {
        $contents = file_get_contents($fileName);
        $this->assertStringContainsString($stream, $contents);
    }

    /**
     * @param string $fileName
     * @param string $stream
     *
     * @return void
     */
    public function assertFileContentsEqual(string $fileName, string $stream): void
    {
        $contents = file_get_contents($fileName);
        $this->assertEquals($contents, $stream);
    }

    /**
     * Returns a directory string with the trailing directory separator
     *
     * @param string $directory
     *
     * @return string
     */
    public function getDirSeparator(string $directory): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns a unique file name
     *
     * @param string $prefix A prefix for the file
     * @param string $suffix A suffix for the file
     *
     * @return string
     */
    public function getNewFileName(
        string $prefix = '',
        string $suffix = 'log'
    ): string {
        $prefix = ($prefix) ? $prefix . '_' : '';
        $suffix = ($suffix) ?: 'log';

        return uniqid($prefix, true) . '.' . $suffix;
    }

    /**
     * Return a long series of strings to be used as a password
     *
     * @return string
     * @throws RandomException
     */
    public function getStrongPassword(): string
    {
        return substr(base64_encode(random_bytes(512)), 0, 128);
    }

    /**
     * Deletes a directory recursively
     *
     * @param string $directory
     */
    public function safeDeleteDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            $dirIterator = new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            );
            $iterator    = new RecursiveIteratorIterator(
                $dirIterator,
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isDir() === true) {
                    $this->safeDeleteDirectory($fileInfo->getRealPath());
                    continue;
                }

                if (
                    empty($fileInfo->getRealPath()) === false &&
                    file_exists($fileInfo->getRealPath())
                ) {
                    unlink($fileInfo->getRealPath());
                }
            }

            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }

    /**
     * Deletes a file if it exists
     *
     * @param string $filename
     *
     * @return void
     */
    public function safeDeleteFile(string $filename): void
    {
        if (file_exists($filename) && is_file($filename)) {
            gc_collect_cycles();
            unlink($filename);
        }
    }
}
