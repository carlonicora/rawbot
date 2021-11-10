<?php
namespace CarloNicora\Minimalism\Raw\Data\Objects;

use CarloNicora\Minimalism\Abstracts\AbstractDataObject;
use CarloNicora\Minimalism\Raw\Enums\RawTrait;
use RuntimeException;

class Ability extends AbstractDataObject
{
    /** @var int  */
    private int $id;

    /** @var int  */
    private int $settingId;

    /** @var RawTrait  */
    private RawTrait $trait;

    /** @var string  */
    private string $name;

    /** @var string  */
    private string $fullName;

    /** @var bool  */
    private bool $canChallenge;

    /** @var bool  */
    private bool $canBeOpposed;

    /** @var bool  */
    private bool $definesInitiative;

    /**
     * @param array|null $data
     * @param int|null $id
     * @param int|null $settingId
     * @param RawTrait|null $trait
     * @param string|null $name
     * @param string|null $fullName
     * @param bool|null $canChallenge
     * @param bool|null $canBeOpposed
     * @param bool|null $definesInitiative
     */
    public function __construct(
        ?array $data = null,
        ?int $id=null,
        ?int $settingId=null,
        ?RawTrait $trait=null,
        ?string $name=null,
        ?string $fullName=null,
        ?bool $canChallenge=false,
        ?bool $canBeOpposed=false,
        ?bool $definesInitiative=false,
    )
    {
        if ($data !== null) {
            parent::__construct($data);
        } else {
            $this->id = $id??throw new RuntimeException('Character Id missing', 412);
            $this->settingId = $settingId??throw new RuntimeException('Ability Id missing', 412);
            $this->trait = $trait??throw new RuntimeException('Trait missing', 412);
            $this->name = $name??throw new RuntimeException('Name missing', 412);
            $this->fullName = $fullName??throw new RuntimeException('Full name missing', 412);
            $this->canChallenge = $canChallenge;
            $this->canBeOpposed = $canBeOpposed;
            $this->definesInitiative = $definesInitiative;
        }
    }

    /**
     * @param array $data
     */
    public function import(
        array $data
    ): void
    {
        $this->id = $data['abilityId'];
        $this->settingId = $data['settingId'];
        $this->trait = RawTrait::from($data['trait']);
        $this->name = $data['name'];
        $this->fullName = $data['fullName'];
        $this->canChallenge = $data['canChallenge']??false;
        $this->canBeOpposed = $data['canBeOpposed']??false;
        $this->definesInitiative = $data['definesInitiative']??false;
    }

    /**
     * @return array
     */
    public function export(): array
    {
        $originalValues = parent::export();

        $data = [
            'abilityId' => $this->id,
            'settingId' => $this->settingId,
            'trait' => $this->trait->value,
            'name' => $this->name,
            'fullName' => $this->fullName,
            'canChallenge' => $this->canChallenge??false,
            'canBeOpposed' => $this->canBeOpposed??false,
            'definesInitiative' => $this->definesInitiative??false,
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
     * @return int
     */
    public function getSettingId(
    ): int
    {
        return $this->settingId;
    }

    /**
     * @return RawTrait
     */
    public function getTrait(
    ): RawTrait
    {
        return $this->trait;
    }

    /**
     * @return string
     */
    public function getName(
    ): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullName(
    ): string
    {
        return $this->fullName;
    }

    /**
     * @return bool
     */
    public function canChallenge(
    ): bool
    {
        return $this->canChallenge;
    }

    /**
     * @return bool
     */
    public function canBeOpposed(
    ): bool
    {
        return $this->canBeOpposed;
    }

    /**
     * @return bool
     */
    public function doesDefinesInitiative(
    ): bool
    {
        return $this->definesInitiative;
    }
}