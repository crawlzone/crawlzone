<?php
declare(strict_types=1);

namespace Crawlzone\Extension;

use Crawlzone\Event\BeforeEngineStarted;
use Crawlzone\Service\StorageService;

class Storage extends Extension
{
    /**
     * @var StorageService
     */
    private $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * @param BeforeEngineStarted $event
     */
    public function beforeEngineStarted(BeforeEngineStarted $event): void
    {
        $this->storageService->importFile(__DIR__ . '/../Storage/Schema/main.sql');
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEngineStarted::class => 'beforeEngineStarted'
        ];
    }
}
