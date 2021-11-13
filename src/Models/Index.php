<?php
namespace CarloNicora\Minimalism\Raw\Models;

use CarloNicora\Minimalism\Raw\Commands\AbilityCommand;
use CarloNicora\Minimalism\Raw\Commands\BonusCommand;
use CarloNicora\Minimalism\Raw\Commands\CampaignCommand;
use CarloNicora\Minimalism\Raw\Commands\CharacterCommand;
use CarloNicora\Minimalism\Raw\Commands\InitiativeCommand;
use CarloNicora\Minimalism\Raw\Commands\RollCommand;
use CarloNicora\Minimalism\Raw\Commands\SessionCommand;
use CarloNicora\Minimalism\Raw\Enums\RawCommand;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractRawModel;
use CarloNicora\Minimalism\Raw\Raw;
use CarloNicora\Minimalism\Raw\Views\BonusDiscordView;
use CarloNicora\Minimalism\Raw\Views\CampaignDiscordView;
use CarloNicora\Minimalism\Raw\Views\CharacterDiscordView;
use CarloNicora\Minimalism\Raw\Views\InitiativeDiscordView;
use CarloNicora\Minimalism\Raw\Views\RollDiscordView;
use CarloNicora\Minimalism\Raw\Views\SessionDiscordView;
use Exception;

class Index extends AbstractRawModel
{
    /**
     * @param Raw $raw
     * @param array|null $payload
     * @return int
     * @throws Exception
     */
    public function post(
        Raw $raw,
        ?array $payload,
    ): int
    {
        try {
            $this->validate(payload: $payload);

            switch (($payload['data']['name'])){
                case RawCommand::Roll->value:
                    $this->command = RollCommand::class;
                    $this->view = RollDiscordView::class;
                    break;
                case RawCommand::Session->value:
                    $this->command = SessionCommand::class;
                    $this->view = SessionDiscordView::class;
                    break;
                case RawCommand::Campaign->value:
                    $this->command = CampaignCommand::class;
                    $this->view = CampaignDiscordView::class;
                    break;
                case RawCommand::Character->value:
                    $this->command = CharacterCommand::class;
                    $this->view = CharacterDiscordView::class;
                    break;
                case RawCommand::Ability->value:
                    $this->command = AbilityCommand::class;
                    $this->view = CharacterDiscordView::class;
                    break;
                case RawCommand::Bonus->value:
                    $this->command = BonusCommand::class;
                    $this->view = BonusDiscordView::class;
                    break;
                case RawCommand::Initiative->value:
                    $this->command = InitiativeCommand::class;
                    $this->view = InitiativeDiscordView::class;
                    break;
            }

            $request = $this->generateRequest($payload);

            $this->document = (new $this->command(
                request: $request,
                raw: $raw,
            ))->execute();
        } catch (Exception $e) {
            $this->document = $this->returnError(exception: $e);
        }

        return 200;
    }
}