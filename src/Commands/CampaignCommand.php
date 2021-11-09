<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataWriters\ServersDataWriter;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;

class CampaignCommand extends AbstractCommand
{
    /**
     * @return Document
     * @throws Exception
     */
    public function execute(
    ): Document
    {
        if (!$this->request->isGM()){
            throw new RuntimeException('Only the GM can manage the sessions!');
        }

        if ($this->request->getPayload()->getParameter(PayloadParameter::Command) === 'start') {
            $this->startSession();
        } else {
            $this->endSession();
        }

        /** @var ServersDataWriter $writeServer */
        $writeServer = MinimalismObjectsFactory::create(ServersDataWriter::class);
        $writeServer->upload($this->request->getServer());

        return $this->response;
    }

    /**
     * @param int|null $serverId
     * @return array
     */
    public function getDefinition(
        ?int $serverId=null,
    ): array
    {
        return [
            'name' => 'campaign',
            'description' => 'Create a campaign in this server. (The user running this command will become the GM)',
            'options' => [
                [
                    'type' => 3,
                    'name' => 'command',
                    'description' => 'Do you want to start or end the session?',
                    'required' => true,
                    'choices' => [
                        [
                            'name' => 'start',
                            'value' => 'start'
                        ],
                        [
                            'name' => 'end',
                            'value' => 'end'
                        ],
                    ]
                ],
            ],
        ];
    }
}