<?php

declare(strict_types=1);

namespace CoderSapient\JsonApi\Cache;

use CoderSapient\JsonApi\Criteria\Criteria;
use JsonApiPhp\JsonApi\ResourceObject;

class InMemoryResourceCache implements ResourceCache
{
    private array $cache = [];

    public function getOne(string $key): ?ResourceObject
    {
        return $this->cache[$key] ?? null;
    }

    /**
     * @return ResourceObject[]
     */
    public function getMany(string ...$keys): array
    {
        $resources = [];

        foreach ($keys as $key) {
            if (isset($this->cache[$key])) {
                $resources[] = $this->cache[$key];
            }
        }

        return $resources;
    }

    /**
     * @return ResourceObject[]
     */
    public function getByCriteria(string $resourceType, Criteria $criteria): array
    {
        return $this->cache[$resourceType][$criteria->key()] ?? [];
    }

    public function set(ResourceObject ...$resources): void
    {
        foreach ($resources as $resource) {
            $this->cache[$resource->key()] = $resource;
        }
    }

    public function setByCriteria(string $resourceType, Criteria $criteria, ResourceObject ...$resources): void
    {
        $this->cache[$resourceType][$criteria->key()] = $resources;
    }

    public function remove(string ...$keys): void
    {
        foreach ($keys as $key) {
            unset($this->cache[$key]);
        }
    }

    public function removeByCriteria(string $resourceType, Criteria $criteria): void
    {
        unset($this->cache[$resourceType][$criteria->key()]);
    }
}
