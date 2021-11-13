<?php
namespace CarloNicora\Minimalism\Raw\Abstracts;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Raw\Exceptions\ErrorException;
use CarloNicora\Minimalism\Raw\Interfaces\CommandInterface;
use CarloNicora\Minimalism\Raw\Objects\Request;
use CarloNicora\Minimalism\Raw\Raw;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordInteractionResponseInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedFooter;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedImage;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

abstract class AbstractCommand implements CommandInterface
{
    /** @var Document  */
    protected Document $response;

    /**
     * @param Request $request
     * @param Raw $raw
     * @throws Exception
     */
    public function __construct(
        protected Request $request,
        protected Raw $raw,
    )
    {
        $this->response = new Document();
        $this->response->meta->add('version', $raw->getVersion());
    }

    /**
     * @param Exception|null $error
     * @param string|null $description
     * @return DiscordInteractionResponseInterface
     */
    public static function generateError(
        ?Exception $error=null,
        ?string $description=null,
    ): DiscordInteractionResponseInterface
    {
        $response = new DiscordMessage();

        if ($error !== null && get_class($error) === ErrorException::class){
            $description = $error->getMessage();
        }

        $response->addEmbed(
            new DiscordEmbed(
                title: 'Error',
                description: $description,
                color: DiscordEmbedInterface::COLOUR_RED,
                footer: new DiscordEmbedFooter('Error'),
                image: new DiscordEmbedImage(url: 'https://media.giphy.com/media/USNlL9p2fxY6Q/source.gif'),
            )
        );

        return $response;
    }
}