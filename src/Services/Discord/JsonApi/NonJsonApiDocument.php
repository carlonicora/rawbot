<?php
namespace CarloNicora\Minimalism\Raw\Services\Discord\JsonApi;

use CarloNicora\JsonApi\Document;
use Exception;

class NonJsonApiDocument extends Document
{
    /**
     * @return array
     * @throws Exception
     */
    public function prepare(
    ): array
    {
        return $this->meta->get('output');
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return 'application/json';
    }
}