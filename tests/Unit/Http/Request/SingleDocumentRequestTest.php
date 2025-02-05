<?php

declare(strict_types=1);

namespace CoderSapient\JsonApi\Tests\Unit\Http\Request;

use CoderSapient\JsonApi\Exception\BadRequestException;
use CoderSapient\JsonApi\Tests\Fake\FakeSingleDocumentRequest;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class SingleDocumentRequestTest extends TestCase
{
    /** @test */
    public function it_should_create_a_valid_document_query(): void
    {
        $request = new FakeSingleDocumentRequest(['include' => 'author,comments']);

        $query = $request->toQuery();

        self::assertSame('1', $query->resourceId());
        self::assertSame('articles', $query->resourceType());
        self::assertTrue($query->includes()->hasInclude('author'));
        self::assertTrue($query->includes()->hasInclude('comments'));
    }

    /** @test */
    public function it_should_throw_an_exception_when_the_include_is_not_supported(): void
    {
        $this->expectException(BadRequestException::class);

        (new FakeSingleDocumentRequest(['include' => 'it_is_not_supported']))->toQuery();
    }

    /** @test */
    public function it_should_throw_an_exception_when_the_include_is_invalid(): void
    {
        $this->expectException(BadRequestException::class);

        (new FakeSingleDocumentRequest(['include' => ['array_is_invalid']]))->toQuery();
    }
}
