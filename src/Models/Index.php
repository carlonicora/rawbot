<?php
namespace CarloNicora\Minimalism\Raw\Models;

use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Models\Discord\Campaign;
use CarloNicora\Minimalism\Raw\Models\Discord\Character;
use CarloNicora\Minimalism\Raw\Models\Discord\Dice;
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
            'roll' => Roll::class,
            'dice' => Dice::class,
            'session' => Session::class,
            'campaign' => Campaign::class,
            RawCommand::Character->value => Character::class,
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