<?php

namespace CarloNicora\Minimalism\Raw\Data\Objects;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Abstracts\AbstractDataObject;
use CarloNicora\Minimalism\Factories\MinimalismObjectsFactory;
use CarloNicora\Minimalism\Raw\Data\DataReaders\AbilitiesDataReader;
use CarloNicora\Minimalism\Raw\Interfaces\ResurceGenerationInterface;
use Exception;
use RuntimeException;

class CharacterAbility extends AbstractDataObject implements ResurceGenerationInterface
{
    /** @var int  */
    private int $characterId;

    /** @var int  */
    private int $abilityId;

    /** @var Ability  */
    private Ability $ability;

    /** @var string  */
    private string $specialisation;

    /** @var int  */
    private int $value;

    /** @var bool  */
    private bool $hasBeenUsed;

    /** @var bool  */
    private bool $hasBeenUpdated;

    /**
     * @param array|null $data
     * @param int|null $characterId
     * @param int|null $abilityId
     * @param string|null $specialisation
     * @param int|null $value
     * @param bool|null $hasBeenUsed
     * @param bool|null $hasBeenUpdated
     * @param int|null $levelOfChildrenToLoad
     * @throws Exception
     */
    public function __construct(
        ?array $data = null,
        ?int $characterId=null,
        ?int $abilityId=null,
        ?string $specialisation='/',
        ?int $value=0,
        ?bool $hasBeenUsed=false,
        ?bool $hasBeenUpdated=false,
        ?int $levelOfChildrenToLoad=0,
    )
    {
        if ($data !== null) {
            parent::__construct(
                data: $data,
                levelOfChildrenToLoad: $levelOfChildrenToLoad,
            );
        } else {
            $this->characterId = $characterId??throw new RuntimeException('Character Id missing', 412);
            $this->abilityId = $abilityId??throw new RuntimeException('Ability Id missing', 412);
            $this->specialisation = $specialisation;
            $this->value = $value;
            $this->hasBeenUsed = $hasBeenUsed;
            $this->hasBeenUpdated = $hasBeenUpdated;
        }

        /** @var AbilitiesDataReader $readAbility */
        $readAbility = MinimalismObjectsFactory::create(AbilitiesDataReader::class);
        $this->ability = $readAbility->byId($this->abilityId);
    }

    /**
     * @param array $data
     */
    public function import(
        array $data
    ): void
    {
        $this->characterId = $data['characterId'];
        $this->abilityId = $data['abilityId'];
        $this->specialisation = $data['specialisation'];
        $this->value = $data['value'];
        $this->hasBeenUsed = $data['used'];
        $this->hasBeenUpdated = $data['wasUpdated'];
    }

    /**
     * @return array
     */
    public function export(): array
    {
        $originalValues = parent::export();

        $data = [
            'characterId' => $this->characterId,
            'abilityId' => $this->abilityId,
            'specialisation' => $this->specialisation,
            'value' => $this->value,
            'used' => $this->hasBeenUsed,
            'wasUpdated' => $this->hasBeenUpdated,
        ];

        return array_merge($originalValues, $data);
    }

    /**
     * @return int
     */
    public function getCharacterId(
    ): int
    {
        return $this->characterId;
    }

    /**
     * @return int
     */
    public function getAbilityId(
    ): int
    {
        return $this->abilityId;
    }

    /**
     * @return Ability
     */
    public function getAbility(
    ): Ability
    {
        return $this->ability;
    }

    /**
     * @return string
     */
    public function getSpecialisation(
    ): string
    {
        return $this->specialisation;
    }

    /**
     * @return int
     */
    public function getValue(
    ): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(
        int $value
    ): void
    {
        $this->value = $value;
    }

    /**
     * @param int $delta
     */
    public function addValue(
        int $delta
    ): void
    {
        $this->value += $delta;
    }

    /**
     * @param int $delta
     */
    public function increaseValue(
        int $delta
    ): void
    {
        $this->value += $delta;
    }

    /**
     * @return bool
     */
    public function hasBeenUsed(
    ): bool
    {
        return $this->hasBeenUsed;
    }

    /**
     *
     */
    public function markAsUsed(
    ): void
    {
        $this->hasBeenUsed = true;
    }

    /**
     *
     */
    public function resetUsage(
    ): void
    {
        $this->hasBeenUsed = false;
    }

    /**
     * @return bool
     */
    public function hasBeenUpdated(
    ): bool
    {
        return $this->hasBeenUpdated;
    }

    /**
     *
     */
    public function markAsUpdated(
    ): void
    {
        $this->hasBeenUpdated = true;
    }

    /**
     *
     */
    public function resetUpdate(
    ): void
    {
        $this->hasBeenUpdated = false;
    }

    /**
     * @return ResourceObject
     * @throws Exception
     */
    public function generateResourceObject(): ResourceObject
    {
        $response = new ResourceObject(
            type: 'character',
        );

        $response->attributes->add('characterId', $this->characterId);
        $response->attributes->add('abilityId', $this->abilityId);
        $response->attributes->add('specialisation', $this->specialisation);
        $response->attributes->add('value', $this->value);
        $response->attributes->add('hasBeenUsed', $this->hasBeenUsed);
        $response->attributes->add('hasBeenUpdated', $this->hasBeenUpdated);

        $response->relationship('ability')->resourceLinkage->add(
            $this->ability->generateResourceObject()
        );

        return $response;
    }
}