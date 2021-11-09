<?php
namespace CarloNicora\Minimalism\Raw\Services\Discord\Payload;

use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;

class Payload
{
    /** @var PayloadGuild  */
    private PayloadGuild $guild;

    /** @var PayloadUser  */
    private PayloadUser $user;

    /** @var string[]  */
    private array $parameters=[];

    /**
     * @param array $payload
     */
    public function __construct(
        array $payload
    )
    {
        $this->guild = new PayloadGuild($payload);
        $this->user = new PayloadUser($payload);

        if (array_key_exists('options', $payload['data'])){
            foreach ($payload['data']['options'] as $parameter){
                $this->parameters[$parameter['name']] = $parameter['value'];
            }
        }
    }

    /**
     * @return PayloadGuild
     */
    public function getGuild(): PayloadGuild
    {
        return $this->guild;
    }

    /**
     * @return PayloadUser
     */
    public function getUser(): PayloadUser
    {
        return $this->user;
    }

    /**
     * @param string $parameterName
     * @return bool
     */
    public function hasParameter(
        string $parameterName,
    ): bool
    {
        return array_key_exists($parameterName, $this->parameters);
    }

    /**
     * @param PayloadParameter $parameter
     * @return string|null
     */
    public function getParameter(
        PayloadParameter $parameter
    ): ?string
    {
        return $this->parameters[$parameter->value]??null;
    }
}