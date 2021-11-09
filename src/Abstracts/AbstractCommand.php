<?php
namespace CarloNicora\Minimalism\Raw\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Raw\Interfaces\CommandInterface;
use CarloNicora\Minimalism\Raw\Objects\Request;

abstract class AbstractCommand implements CommandInterface
{
    /** @var Document  */
    protected Document $response;

    /**
     * @param Request $request
     */
    public function __construct(
        protected Request $request,
    )
    {
        $this->response = new Document();
    }
}