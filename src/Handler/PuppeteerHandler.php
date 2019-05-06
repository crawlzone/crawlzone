<?php
declare(strict_types=1);


namespace Crawlzone\Handler;


use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PuppeteerHandler implements Handler
{
    /** @var Promise */
    private $promises;

    /**  @var Process */
    private $processes;

    private $isNodeInstalled;

    /**
     * @return void
     */
    public function execute(): void
    {
       while(! empty($this->processes)) {

            /** @var Process $process */
            foreach ($this->processes as $pid => $process) {
                if($process->isRunning()) {
                    continue;
                }
                //todo: use getIncrementalOutput()
                if ($process->isSuccessful()) {
                    $content = $process->getOutput();
                    $response = new Response();
                    $response = $response->withBody(\GuzzleHttp\Psr7\stream_for($content));


                    $this->promises[$pid]->resolve($response);
                } else {
                    $this->promises[$pid]->reject(new \Exception($process->getErrorOutput()));
                }

                unset($this->processes[$pid]);
                unset($this->promises[$pid]);

            }
            usleep(1000);
        }
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options): PromiseInterface
    {
        $arguments = json_encode([
            'uri' => (string) $request->getUri()
        ]);

        $this->guardNodeJsInstallation();

        //@todo: check if the puppeteer library is installed
        $cmd = "node " . __DIR__ . "/../browser.js " . escapeshellarg($arguments);

        $process = Process::fromShellCommandline($cmd);

        $pid = spl_object_hash($process);

        $process->start();

        $this->processes[$pid] = $process;

        $this->promises[$pid] = new Promise([$this,'execute']);

        return $this->promises[$pid];
    }

    private function guardNodeJsInstallation(): void
    {
        if($this->isNodeInstalled) {
            return;
        }

        $process = Process::fromShellCommandline("node -v");
        $process->run();

        if(! $process->isSuccessful()) {
            throw new \RuntimeException("Please install Node.js - https://nodejs.org/");
        }

        $this->isNodeInstalled = true;

        return;
    }
}