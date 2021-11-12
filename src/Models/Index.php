<?php
namespace CarloNicora\Minimalism\Raw\Models;

use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Models\Discord\Ability;
use CarloNicora\Minimalism\Raw\Models\Discord\Bonus;
use CarloNicora\Minimalism\Raw\Models\Discord\Campaign;
use CarloNicora\Minimalism\Raw\Models\Discord\Character;
use CarloNicora\Minimalism\Raw\Models\Discord\Roll;
use CarloNicora\Minimalism\Raw\Models\Discord\Session;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractRawModel;
use Exception;

class Index extends AbstractRawModel
{
    /**
     * @param array|null $payload
     * @return int
     * @throws Exception
     */
    public function post(
        ?array $payload,
    ): int
    {
        $this->validate(payload: $payload);

        $model = match ($payload['data']['name']) {
            RawCommand::Roll->value => Roll::class,
            RawCommand::Session->value => Session::class,
            RawCommand::Campaign->value => Campaign::class,
            RawCommand::Character->value => Character::class,
            RawCommand::Ability->value => Ability::class,
            RawCommand::Bonus->value => Bonus::class,
        };

        $this->redirect(
            modelClass: $model,
            function: 'post',
            namedParameters: [
                'payload' => $payload
            ],
        );

        return 302;
    }
}