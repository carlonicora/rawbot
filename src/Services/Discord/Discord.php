<?php
namespace CarloNicora\Minimalism\Raw\Services\Discord;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Interfaces\TransformerInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\JsonApi\NonJsonApiDocument;
use Exception;

class Discord implements ServiceInterface, TransformerInterface
{
    /**
     *
     */
    public function initialise(): void
    {
    }

    /**
     *
     */
    public function destroy(): void
    {
    }

    /**
     * @param Document $document
     * @param string $viewFile
     * @return string
     * @throws Exception
     */
    public function transform(
        Document $document,
        string $viewFile,
    ): string
    {
        $transformer = new $viewFile(
            $document,
        );

        $response = new NonJsonApiDocument();
        $response->meta->add('output', $transformer->export());

        return $response->export();
    }

    /**
     * @return string
     */
    public function getContentType(
    ): string
    {
        return 'application/json';
    }
}