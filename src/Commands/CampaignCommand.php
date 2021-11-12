<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Data\DataWriters\ServersDataWriter;
use CarloNicora\Minimalism\Raw\Data\Objects\Server;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Enums\RawError;
use CarloNicora\Minimalism\Raw\Factories\CommandOptionsFactory;
use CarloNicora\Minimalism\Raw\Services\Discord\ApplicationCommands\ApplicationCommand;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\ApplicationCommandInterface;
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
     * @return ApplicationCommandInterface
     */
    public function getDefinition(
        ?int $serverId=null,
    ): ApplicationCommandInterface
    {
        $response = new ApplicationCommand(
            id: RawCommand::Campaign->value,
            applicationId: '??',
            name: RawCommand::Campaign->value,
            description: 'Create a campaign in this server. (The user running this command will become the GM)',
        );
        $response->addOption(CommandOptionsFactory::getNameSubOption('The name of the campaign', true));

        return $response;
    }
}