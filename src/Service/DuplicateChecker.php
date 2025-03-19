<?php

namespace App\Service;

use App\Dto\RawData\RawData;
use DateInterval;
use Psr\Cache\CacheItemPoolInterface;

final readonly class DuplicateChecker
{
    public function __construct(
        private CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @param RawData<object> $object
     */
    public function isDuplicate(RawData $object): bool
    {
        return $this->cache->getItem($this->getKey($object))->isHit();
    }

    public function markAsProcessed(RawData $object): void
    {
        $cacheItem = $this->cache->getItem($this->getKey($object));
        $cacheItem->set(true);
        $cacheItem->expiresAfter(new DateInterval('PT1M'));
        $this->cache->save($cacheItem);
    }

    /**
     * @param RawData<object> $object
     */
    private function getKey(RawData $object): string
    {
        if (property_exists($object->data, 'id')) {
            $hash = $object->data->id;
        } else {
            $hash = hash('sha512', serialize($object->data));
        }

        return "webhooks.handled.{$object->schema}.{$object->table}.{$object->operation->value}.{$hash}";
    }
}
