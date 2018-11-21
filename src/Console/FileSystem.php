<?php
declare(strict_types=1);


namespace Crawlzone\Console;

use RuntimeException;

/**
 * Simple wrapper for global file read write functions for testing purposes
 *
 * @package Crawlzone\Console
 * @internal
 */
class FileSystem
{
    /**
     * @param string $path
     * @return string
     */
    public function fileGetContent(string $path): string
    {
        if (false === $content = @file_get_contents($path)) {
            throw new RuntimeException(sprintf('Failed to read file "%s".', $path));
        }

        return $content;
    }

    /**
     * @param string $path
     * @param string $content
     */
    public function filePutContents(string $path, string $content): void
    {
        if (false === @file_put_contents($path, $content)) {
            throw new RuntimeException(sprintf('Failed to write file "%s".', $path));
        }
    }
}
