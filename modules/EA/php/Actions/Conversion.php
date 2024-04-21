<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace EA\Actions\Conversion;

require_once(__DIR__ . '/.././../../BX/php/Action.php');

class KeepReturnFromConversionState extends \BX\Action\BaseActionCommand
{
    protected $privateStateIdInit;
    protected $privateStateId;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->privateStateIdInit) {
            if ($notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                $player = self::getMgr('player')->getByPlayerId($this->playerId);
                $this->privateStateId = $player->playerState;
            } else {
                $this->privateStateId = null;
            }
            $this->privateStateIdInit = true;
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->returnFromConversionStateId = $this->privateStateId;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class ConvertSprout extends \BX\Action\BaseActionCommand
{
    private $payedSproutList;
    private $nbGainedSoil;
    private $undoTableau;

    public function __construct(int $playerId, array $payedSproutList)
    {
        parent::__construct($playerId);
        $this->nbGainedSoil = 0;
        $this->payedSproutList = $payedSproutList;
        if (count($payedSproutList) % 2 != 0) {
            throw new \BgaSystemException('BUG! payedSproutList is not even');
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $nbPayedSprout = 0;
        $payedSprout = [];
        for ($i = 0; $i < count($this->payedSproutList); $i += 2) {
            $payedSprout[$this->payedSproutList[$i]] = $this->payedSproutList[$i + 1];
            $nbPayedSprout += $this->payedSproutList[$i + 1];
        }
        if ($nbPayedSprout <= 0 || ($nbPayedSprout % 3) != 0) {
            throw new \BgaUserException($notifier->_('You must select a multiple of 3 sprouts (3, 6, 9, ...)'));
        }
        $this->nbGainedSoil = 2 * floor($nbPayedSprout / 3);
        foreach ($cardMgr->getPlayerTableauCountSprout($this->playerId) as $count) {
            if (!\array_key_exists($count->cardId, $payedSprout)) {
                continue;
            }
            if ($payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException('BUG! payedSprout is negative');
            }
            if ($count->count - $payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException("BUG! Payed too many sprouts on cardId {$count->cardId}");
            }
            $card = $cardMgr->getCardById($count->cardId);
            $card->modifyAction();
            $card->removeSprout($payedSprout[$count->cardId]);
            unset($payedSprout[$count->cardId]);
        }
        if (count($payedSprout) > 0) {
            throw new \BgaSystemException('BUG! payedSprout on invalid cards');
        }
        self::getMgr('player_state')->incStatNbSproutsConverted($this->playerId, $nbPayedSprout);
        self::getMgr('player_state')->incStatNbSoilGained($this->playerId, $this->nbGainedSoil);
        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            clienttranslate('${player_name} converts ${nbPayedSprout} ${sproutIcon} to ${nbGainedSoil} ${soilIcon}'),
            [
                'nbPayedSprout' => $nbPayedSprout,
                'sproutIcon' => clienttranslate('sprout(s)'),
                'soilIcon' => clienttranslate('soil'),
                'nbGainedSoil' => $this->nbGainedSoil,
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoTableau !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
        }
    }

    public function getNbGainedSoil()
    {
        return $this->nbGainedSoil;
    }
}