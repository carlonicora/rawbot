<?php
namespace CarloNicora\Minimalism\Raw\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Raw\Exceptions\ErrorException;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedAuthorInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedFooterInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedImageInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordEmbedThumbnailInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Interfaces\DiscordInteractionResponseInterface;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbed;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedAuthor;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedFooter;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedImage;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordEmbedThumbnail;
use CarloNicora\Minimalism\Raw\Services\Discord\Messages\DiscordMessage;
use Exception;

class DiscordMessageFactory
{
    /**
     * @param string $description
     * @return Document
     * @throws Exception
     */
    public static function generateErrorDocument(
        string $description,
    ): Document
    {
        $response = new Document();

        $error = new ResourceObject(
            type: 'error',
        );
        $error->attributes->add(
            name: 'description',
            value: $description
        );

        $response->addResource(
            resource: $error,
        );

        return $response;
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

        if ($description !== null) {
            $response->addEmbed(
                new DiscordEmbed(
                    title: 'Error',
                    description: $description,
                    color: DiscordEmbedInterface::COLOUR_RED,
                    footer: self::createFooter(
                        type: 'Error'
                    ),
                        image: self::createImage(
                        url: 'https://media.giphy.com/media/USNlL9p2fxY6Q/source.gif'
                    ),
                )
            );
        } elseif ($error !== null){
            $response->addEmbed(
                new DiscordEmbed(
                    title: 'RAW Error',
                    description: 'If you give this to your GM, maybe they\'ll know what to do...' . PHP_EOL . '`' . $error->getMessage() . '`',
                    color: DiscordEmbedInterface::COLOUR_RED,
                    footer: self::createFooter(
                        type: 'Error'
                    ),
                        image: self::createImage(
                        url: 'https://media.giphy.com/media/9A6L1yovLytAJtJ7fs/source.gif'
                    ),
                    )
                );
        }

        return $response;
    }

    /**
     * @param array|null $character
     * @return DiscordEmbedThumbnailInterface|null
     */
    public static function createCharacterThumbnail(
        ?array $character,
    ): ?DiscordEmbedThumbnailInterface
    {
        if ($character === null){
            return null;
        }

        return new DiscordEmbedThumbnail(
            $character['thumbnail']
        );
    }

    /**
     * @param array|null $character
     * @return DiscordEmbedAuthorInterface|null
     */
    public static function createAuthor(
        ?array $character,
    ): ?DiscordEmbedAuthorInterface
    {
        if ($character === null){
            return null;
        }

        return new DiscordEmbedAuthor(
            name: $character['name'],
            icon_url: $character['thumbnail'],
        );
    }

    /**
     * @param string $type
     * @return DiscordEmbedFooterInterface
     */
    public static function createFooter(
        string $type
    ): DiscordEmbedFooterInterface
    {
        return new DiscordEmbedFooter(
            text: $type,
            icon_url: 'https://previews.123rf.com/images/martialred/martialred1512/martialred151200052/49796805-20-sided-20d-dice-line-art-icon-for-apps-and-websites.jpg'
        );
    }

    /**
     * @param string $url
     * @return DiscordEmbedImage
     */
    public static function createImage(
        string $url,
    ): DiscordEmbedImage
    {
        return new DiscordEmbedImage(url: $url);
    }

    /**
     * @param int $type
     * @return DiscordEmbedImageInterface|null
     */
    public static function createRollImage(
        int $type
    ): ?DiscordEmbedImageInterface
    {
        if ($type === DiceRoller::CRITICAL_NONE){
            return null;
        }

        $url = match($type) {
            DiceRoller::CRITICAL_SUCCESS => 'https://media.giphy.com/media/Z9KdRxSrTcDHGE6Ipf/giphy.gif',
            DiceRoller::CRITICAL_FAILURE => 'https://vignette.wikia.nocookie.net/kingsway-role-playing-group/images/a/ab/A7c1d56e7cdb84ee25e6769d9c7b9910--tabletop-rpg-tabletop-games.jpg',
        };

        return new DiscordEmbedImage(
            url: $url
        );
    }
}