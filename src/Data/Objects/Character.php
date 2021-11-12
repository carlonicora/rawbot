<?php

namespace CarloNicora\Minimalism\Raw\Data\Objects;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractDataObject;
use CarloNicora\Minimalism\Raw\Enums\RawTrait;
use CarloNicora\Minimalism\Raw\Interfaces\ResurceGenerationInterface;
use Exception;
use RuntimeException;

class Character extends AbstractDataObject implements ResurceGenerationInterface
{
    /** @var int|null  */
    private ?int $id=null;

    /** @var int  */
    private int $serverId;

    /** @var string|null  */
    private ?string $userId=null;

    /** @var string|null  */
    private ?string $username=null;

    /** @var bool  */
    private bool $isNPC;

    /** @var string|null  */
    private ?string $shortname=null;

    /** @var string|null  */
    private ?string $name=null;

    /** @var int  */
    private int $body;

    /** @var int  */
    private int $mind;

    /** @var int  */
    private int $spirit;

    /** @var int  */
    private int $bonus;

    /** @var int  */
    private int $damages;

    /** @var string|null  */
    private ?string $description;

    /** @var bool  */
    private bool $automaticallyAcceptChallenges;

    /** @var string|null  */
    private ?string $thumbnail;

    /**
     * @param array|null $data
     * @param int|null $id
     * @param int|null $serverId
     * @param string|null $userId
     * @param string|null $username
     * @param bool|null $isNPC
     * @param string|null $shortname
     * @param string|null $name
     * @param int|null $body
     * @param int|null $mind
     * @param int|null $spirit
     * @param int|null $bonus
     * @param int|null $damages
     * @param string|null $description
     * @param bool|null $automaticallyAcceptChallenges
     * @param string|null $thumbnail
     */
    public function __construct(
        ?array $data = null,
        ?int $id=null,
        ?int $serverId=null,
        ?string $userId=null,
        ?string $username=null,
        ?bool $isNPC=false,
        ?string $shortname=null,
        ?string $name=null,
        ?int $body=0,
        ?int $mind=0,
        ?int $spirit=0,
        ?int $bonus=0,
        ?int $damages=0,
        ?string $description=null,
        ?bool $automaticallyAcceptChallenges=false,
        ?string $thumbnail=null,
    )
    {
        if ($data !== null) {
            parent::__construct($data);
        } else {
            $this->id = $id;
            $this->serverId = $serverId??throw new RuntimeException('Server Id missing', 412);
            $this->userId = $userId;
            $this->username = $username;
            $this->isNPC = $isNPC;
            $this->shortname = $shortname??throw new RuntimeException('User short name missing', 412);
            $this->name = $name;
            $this->body = $body;
            $this->mind = $mind;
            $this->spirit = $spirit;
            $this->bonus = $bonus;
            $this->damages = $damages;
            $this->description = $description;
            $this->automaticallyAcceptChallenges = $automaticallyAcceptChallenges;
            $this->thumbnail = $thumbnail;
        }
    }

    /**
     * @param array $data
     */
    public function import(
        array $data
    ): void
    {
        $this->id = $data['characterId'];
        $this->serverId = $data['serverId'];
        $this->userId = $data['discordUserId'];
        $this->username = $data['discordUserName'];
        $this->isNPC = $data['isNPC'];
        $this->shortname = $data['shortName'];
        $this->name = $data['name'];
        $this->body = $data['body'];
        $this->mind = $data['mind'];
        $this->spirit = $data['spirit'];
        $this->bonus = $data['bonusPoints'];
        $this->damages = $data['damages'];
        $this->description = $data['description'];
        $this->automaticallyAcceptChallenges = $data['automaticallyAcceptChallenges'];
        $this->thumbnail = $data['thumbnail'];
    }

    /**
     * @return array
     */
    public function export(): array
    {
        $originalValues = parent::export();

        $data = [
            'characterId' => $this->id,
            'serverId' => $this->serverId,
            'discordUserId' => $this->userId,
            'discordUserName' => $this->username,
            'isNPC' => $this->isNPC,
            'shortName' => $this->shortname,
            'name' => $this->name,
            'body' => $this->body,
            'mind' => $this->mind,
            'spirit' => $this->spirit,
            'bonusPoints' => $this->bonus,
            'damages' => $this->damages,
            'description' => $this->description,
            'automaticallyAcceptChallenges' => $this->automaticallyAcceptChallenges,
            'thumbnail' => $this->thumbnail,
        ];

        return array_merge($originalValues, $data);
    }

    /**
     * @return bool
     */
    public function isNew(
    ): bool
    {
        return $this->id === null;
    }

    /**
     * @return int
     */
    public function getId(
    ): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(
        int $id,
    ): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getServerId(
    ): int
    {
        return $this->serverId;
    }

    /**
     * @param int $serverId
     */
    public function setServerId(
        int $serverId,
    ): void
    {
        $this->serverId = $serverId;
    }

    /**
     * @return string|null
     */
    public function getUsername(
    ): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     */
    public function setUsername(
        ?string $username,
    ): void
    {
        $this->username = $username;
    }

    /**
     * @return bool
     */
    public function isNPC(
    ): bool
    {
        return $this->isNPC;
    }

    /**
     *
     */
    public function setAsNPC(
    ): void
    {
        $this->isNPC = true;
    }

    /**
     *
     */
    public function setAsPC(
    ): void
    {
        $this->isNPC = false;
    }

    /**
     * @return string|null
     */
    public function getShortname(
    ): ?string
    {
        return $this->shortname;
    }

    /**
     * @param string|null $shortname
     */
    public function setShortname(
        ?string $shortname,
    ): void
    {
        $this->shortname = $shortname;
    }

    /**
     * @return string|null
     */
    public function getName(
    ): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(
        ?string $name,
    ): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getBody(
    ): int
    {
        return $this->body;
    }

    /**
     * @param int $body
     */
    public function setBody(
        int $body,
    ): void
    {
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getMind(
    ): int
    {
        return $this->mind;
    }

    /**
     * @param int $mind
     */
    public function setMind(
        int $mind,
    ): void
    {
        $this->mind = $mind;
    }

    /**
     * @return int
     */
    public function getSpirit(
    ): int
    {
        return $this->spirit;
    }

    /**
     * @param int $spirit
     */
    public function setSpirit(
        int $spirit,
    ): void
    {
        $this->spirit = $spirit;
    }

    /**
     * @return int
     */
    public function getBonus(
    ): int
    {
        return $this->bonus;
    }

    /**
     * @param int $bonus
     */
    public function setBonus(
        int $bonus,
    ): void
    {
        $this->bonus = $bonus;
    }

    /**
     * @param int $bonus
     */
    public function addBonus(
        int $bonus,
    ): void
    {
        $this->bonus += $bonus;
    }

    /**
     * @return int
     */
    public function getDamages(
    ): int
    {
        return $this->damages;
    }

    /**
     * @param int $damages
     */
    public function setDamages(
        int $damages,
    ): void
    {
        $this->damages = $damages;
    }

    /**
     * @param int $damages
     */
    public function addDamages(
        int $damages,
    ): void
    {
        $this->damages += $damages;
    }

    /**
     * @return string|null
     */
    public function getDescription(
    ): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(
        ?string $description,
    ): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function doesAutomaticallyAcceptChallenges(
    ): bool
    {
        return $this->automaticallyAcceptChallenges;
    }

    /**
     * @param bool $automaticallyAcceptChallenges
     */
    public function setAutomaticallyAcceptChallenges(
        bool $automaticallyAcceptChallenges,
    ): void
    {
        $this->automaticallyAcceptChallenges = $automaticallyAcceptChallenges;
    }

    /**
     * @return string|null
     */
    public function getThumbnail(
    ): ?string
    {
        return $this->thumbnail;
    }

    /**
     * @param string|null $thumbnail
     */
    public function setThumbnail(
        ?string $thumbnail,
    ): void
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @param RawTrait $trait
     * @return int
     */
    public function getTraitValue(
        RawTrait $trait,
    ): int
    {
        return match ($trait) {
            RawTrait::Body => $this->body,
            RawTrait::Mind => $this->mind,
            RawTrait::Spirit => $this->spirit,
        };
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function generateResourceObject(): ResourceObject
    {
        $response = new ResourceObject(
            type: 'character',
            id: $this->id,
        );

        $response->attributes->add('serverId', $this->serverId);
        $response->attributes->add('userId', $this->userId);
        $response->attributes->add('isNPC', $this->isNPC);
        $response->attributes->add('username', $this->username);
        $response->attributes->add('shortName', $this->shortname);
        $response->attributes->add('name', $this->name??$this->shortname);
        $response->attributes->add('body', $this->body);
        $response->attributes->add('mind', $this->mind);
        $response->attributes->add('spirit', $this->spirit);
        $response->attributes->add('bonus', $this->bonus);
        $response->attributes->add('damages', $this->damages);
        $response->attributes->add('description', $this->description);
        $response->attributes->add('automaticallyAcceptChallenges', $this->automaticallyAcceptChallenges);
        $response->attributes->add('thumbnail', $this->thumbnail);

        return $response;
    }
}