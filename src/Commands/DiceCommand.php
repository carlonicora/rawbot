<?php
namespace CarloNicora\Minimalism\Raw\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Raw\Abstracts\AbstractCommand;
use CarloNicora\Minimalism\Raw\Enums\PayloadParameter;
use CarloNicora\Minimalism\Raw\Enums\RawDocument;
use CarloNicora\Minimalism\Raw\Helpers\DiceRoller;
use Exception;

class DiceCommand extends AbstractCommand
{
    /**
     * @return Document
     * @throws Exception
     */
    public function execute(
    ): Document
    {
        $dice = $this->request->getPayload()->getParameter(PayloadParameter::Dice);
        $bonus = $this->request->getPayload()->getParameter(PayloadParameter::Bonus);

        [$quantity,$sides] = explode('d', $dice);

        $total = 0;

        for ($round = 1; $round <= $quantity; $round++){
            $result = DiceRoller::simpleRoll($sides);

            $total += $result;

            $diceResource = new ResourceObject(
                type: RawDocument::Dice->value,
                id: $round,
            );
            $diceResource->attributes->add('type', 'd' . $sides);
            $diceResource->attributes->add('roll', $result);
            $this->response->addResource($diceResource);
        }

        if ($bonus !== null){
            $this->response->meta->add('bonus', $bonus);
            $total += (int)$bonus;
        }

        $this->response->meta->add('result', $total);
        $this->response->meta->add('dice', $dice);

        return $this->response;
    }

    /**
     * @param int|null $serverId
     * @return array
     */
    public function getDefinition(
        ?int $serverId=null,
    ): array
    {
        return [
            'name' => 'dice',
            'description' => 'Roll one or more dices.',
            'options' => [
                [
                    'type' => 3,
                    'name' => 'dice',
                    'description' => 'Specify the number of dices and the sides (1d20, 3d6, ...)',
                    'required' => true,
                ],
                [
                    'type' => 4,
                    'name' => 'bonus',
                    'description' => 'Advantage or disadvantage to the roll',
                    'required' => false,
                ],
            ],
        ];
    }
}