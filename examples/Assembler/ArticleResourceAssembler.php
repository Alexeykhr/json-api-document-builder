<?php

declare(strict_types=1);

namespace CoderSapient\JsonApi\Examples\Assembler;

use CoderSapient\JsonApi\Examples\Model\Article;
use JsonApiPhp\JsonApi\Attribute;
use JsonApiPhp\JsonApi\ResourceIdentifier;
use JsonApiPhp\JsonApi\ResourceObject;
use JsonApiPhp\JsonApi\ToOne;

final class ArticleResourceAssembler
{
    /**
     * @return ResourceObject[]
     */
    public function toResources(Article ...$articles): array
    {
        $collect = [];

        foreach ($articles as $article) {
            $collect[] = $this->toResource($article);
        }

        return $collect;
    }

    public function toResource(Article $article): ResourceObject
    {
        return new ResourceObject(
            'articles',
            $article->id(),
            new Attribute('title', $article->title()),
            new ToOne(
                'author',
                new ResourceIdentifier('users', $article->authorId()),
            ),
        );
    }
}
