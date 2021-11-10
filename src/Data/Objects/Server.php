<?php
namespace CarloNicora\Minimalism\Raw\Data\Objects;

use CarloNicora\Minimalism\Abstracts\AbstractDataObject;
use RuntimeException;

class Server extends AbstractDataObject
{
    /** @var int|null  */
    private ?int $id=null;

    /** @var int  */
    private int $settingId;

    /** @var string  */
    private string $serverId;

    /** @var string  */
    private string $gm;

    /** @var string|null  */
    private ?string $campaign=null;

    /** @var bool  */
    private bool $inSession;

    public function __construct(
        ?array $data = null,
        ?int $id=null,
        ?int $settingId=1,
        ?string $serverId=null,
        ?string $gm=null,
        ?string $campaign=null,
        ?bool $inSession=false,
    )
    {
        if ($data !== null) {
            parent::__construct($data);
        } else {
            $this->id = $id;
            $this->settingId = $settingId;
            $this->serverId = $serverId??throw new RuntimeException('Discord Server Id missing', 412);
            $this->gm = $gm??throw new RuntimeException('Discord User Id missing', 412);
            $this->campaign = $campaign;
            $this->inSession = $inSession;
        }
    }

    /**
     * @param array $data
     */
    public function import(
        array $data
    ): void
    {
        $this->id = $data['serverId'];
        $this->settingId = $data['settingId'];
        $this->serverId = $data['discordServerId'];
        $this->gm = $data['discordUserId'];
        $this->campaign = $data['campaignName'];
        $this->inSession = $data['inSession'];
    }

    /**
     * @return array
     */
    public function export(): array
    {
        $originalValues = parent::export();

        $data = [
            'serverId' => $this->id,
            'settingId' => $this->settingId,
            'discordServerId' => $this->serverId,
            'discordUserId' => $this->gm,
            'campaignName' => $this->campaign,
            'inSession' => $this->inSession,
        ];

        return array_merge($originalValues, $data);
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
    public function getSettingId(
    ): int
    {
        return $this->settingId;
    }

    /**
     * @param int $settingId
     */
    public function setSettingId(
        int $settingId,
    ): void
    {
        $this->settingId = $settingId;
    }

    /**
     * @return string
     */
    public function getServerId(
    ): string
    {
        return $this->serverId;
    }

    /**
     * @param string $serverId
     */
    public function setServerId(
        string $serverId,
    ): void
    {
        $this->serverId = $serverId;
    }

    /**
     * @return string
     */
    public function getGm(
    ): string
    {
        return $this->gm;
    }

    /**
     * @param string $gm
     */
    public function setGm(
        string $gm,
    ): void
    {
        $this->gm = $gm;
    }

    /**
     * @return string|null
     */
    public function getCampaign(
    ): ?string
    {
        return $this->campaign;
    }

    /**
     * @param string|null $campaign
     */
    public function setCampaign(
        ?string $campaign
    ): void
    {
        $this->campaign = $campaign;
    }

    /**
     * @return bool
     */
    public function isInSession(
    ): bool
    {
        return $this->inSession;
    }

    /**
     *
     */
    public function startSession(
    ): void
    {
        $this->inSession = true;
    }

    /**
     *
     */
    public function endSession(
    ): void
    {
        $this->inSession = false;
    }
}