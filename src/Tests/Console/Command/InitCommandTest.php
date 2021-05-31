<?php
namespace Crawlzone\Tests\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Crawlzone\Console\Command\InitCommand;
use PHPUnit\Framework\TestCase;
use Crawlzone\Console\FileSystem;

class InitCommandTest extends TestCase
{
    public function testGeneratedConfig()
    {
        /** @var FileSystem $fileSystem */
        $fileSystem = $this->getMockBuilder(FileSystem::class)->getMock();

        $command = new InitCommand($fileSystem);

        $configYmlTemplate = $command->getYmlTemplate();

        $expected = <<<YML
start_uri:
 - http://test.com
 - http://mytest.com
concurrency: 10
save_progress_in: memory
request_options: 
  verify: true
  cookies: true
  allow_redirects: false
  debug: false

filter:
  robotstxt_obey: false
  allow:
    - testpage.html
    - testfolder
  allow_domains:
    - mydomain.com
    - yourdomain.com
  deny_domains:
    - otherdomain.com
  deny:
    - thatpage.html

autothrottle:
  enabled: true
  min_delay: 0
  max_delay: 60
YML;

        $this->assertEquals($expected, $configYmlTemplate);
    }

    public function testInitCommand()
    {
        $application = new Application;

        /** @var FileSystem $fileSystem */
        $fileSystem = $this->getMockBuilder(FileSystem::class)->getMock();

        $application->add(new InitCommand($fileSystem));

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        // Cleanup
        $this->assertStringContainsString('Created config file:', $output);
    }
}
