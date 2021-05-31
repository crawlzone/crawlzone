<?php

namespace Crawlzone\Tests\Console;

use PHPUnit\Framework\TestCase;
use Crawlzone\Console\FileSystem;

class FileSystemTest extends TestCase
{
    public function testFileGetContent()
    {
        $fileSystem = new FileSystem;

        $content = $fileSystem->fileGetContent(__DIR__ . "/../data/site1.local/web/robots.txt");

        $this->assertStringContainsString("User-agent: *", $content);
        $this->assertStringContainsString("Disallow: /", $content);
    }

    public function testFileGetContentException()
    {
        $this->expectException(\RuntimeException::class);

        $fileSystem = new FileSystem;

        $content = $fileSystem->fileGetContent(__DIR__ . "/non-existing-file");
    }

    public function testFilePutContent()
    {
        $fileSystem = new FileSystem;

        $file =__DIR__ . "/../data/testfileputcontent";

        $fileSystem->filePutContents($file, "test");

        $content = $fileSystem->fileGetContent($file);

        $this->assertStringContainsString("test", $content);

        //Cleanup
        unlink($file);
    }
}
