<?php
declare(strict_types=1);


namespace Crawlzone\Console\Command;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Crawlzone\Client;
use Crawlzone\Console\FileSystem;
use Crawlzone\Extension\ConsoleLogging;

/**
 * @package Crawlzone\Console\Command
 */
class StartCommand extends Command
{
    /**
     * @var null|FileSystem
     */
    private $filesystem;

    /**
     * StartCommand constructor.
     * @param null|FileSystem $filesystem
     */
    public function __construct(? FileSystem $filesystem = null)
    {
        parent::__construct();

        if (! $filesystem) {
            $filesystem = new FileSystem;
        }

        $this->filesystem = $filesystem;
    }

    protected function configure(): void
    {
        $this->setName('start')
            ->setDescription('Starts the crawler using provided configuration file.')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'The path to the configuration file.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $input->getOption('config') ?? "./crawler.yml";

        $client = $this->getClient($configPath);

        $verbosityLevelMap = [
            LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG,
        ];
        $client->addExtension(new ConsoleLogging(new ConsoleLogger($output, $verbosityLevelMap)));

        $client->run();

        return 0;
    }

    /**
     * @param string $path
     * @return array
     */
    private function getConfigFromFile(string $path): array
    {
        return Yaml::parse($this->filesystem->fileGetContent($path));
    }

    /**
     * @param string $configPath
     * @return Client
     */
    private function getClient(string $configPath): Client
    {
        $config = $this->getConfigFromFile($configPath);

        return new Client($config);
    }
}
