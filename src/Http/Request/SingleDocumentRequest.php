<?php

declare(strict_types=1);

namespace CoderSapient\JsonApi\Http\Request;

use CoderSapient\JsonApi\Document\Builder\SingleDocumentQuery;

trait SingleDocumentRequest
{
    use JsonApiRequest;

    public function toQuery(): SingleDocumentQuery
    {
        $this->ensureQueryParamsIsValid();

        return (new SingleDocumentQuery(
            $this->resourceId(),
            $this->resourceType(),
        ))->setIncludes($this->includes());
    }

    abstract protected function resourceId(): string;

    protected function supportedQueryParams(): array
    {
        return [$this->queryInclude];
    }
}
