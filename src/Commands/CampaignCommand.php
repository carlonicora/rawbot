<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataWriters\ServersDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\Server;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use Exception;
use RuntimeException;

class CampaignCommand extends AbstractCommand
{
    /**
     * @return Document
     * @throws Exception
     */
    public function execute(
    ): Document
    {
        if ($this->request->getServer() !== null){
            throw new RuntimeException(RawError::CampaignAlreadyInitialised->getMessage());
        }

        $name = $this->request->getPayload()?->getParameter(PayloadParameter::Name);

        $server = new Server(
            serverId: $this->request->getPayload()?->getGuild()->getId(),
            gm: $this->request->getPayload()?->getUser()->getId(),
            campaign: $name,
        );

        /** @var ServersDataWriter $writeServer */
        $writeServer = MinimalismObjectsFactory::create(ServersDataWriter::class);
        $writeServer->upload($server);

        $campaignResource = new ResourceObject(
            type: 'campaign'
        );
        $campaignResource->attributes->add('name', $name);

        $this->response->addResource(
            $campaignResource
        );

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
                    'name' => PayloadParameter::Name->value,
                    'description' => 'The name of the campaign',
                    'required' => true,
                ],
            ],
        ];
    }
}