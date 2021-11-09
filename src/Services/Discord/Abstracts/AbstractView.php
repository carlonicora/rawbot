<?php
namespace CarloNicora\Minimalism\Raw\Services\Discord\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Raw\Factories\DiscordMessageFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ExportableInterface;
use Exception;

abstract class AbstractView implements ExportableInterface
{
    /**
     * @param Document $document
     */
    public function __construct(
        protected Document $document,
    )
    {
    }

    /**
     * @return array
     * @throws Exception
     */
    final public function export(
    ): array
    {
        if ($this->document->resources[0]->type === 'error'){
            return DiscordMessageFactory::generateError(
                description: $this->document->resources[0]->attributes->get('description')
            )->export();
        }

        return $this->exportView();
    }

    /**
     * @return array
     */
    abstract public function exportView():array;
}