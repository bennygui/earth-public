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

namespace BX\LastRound;

require_once('Globals.php');
require_once('Action.php');

const NTF_UPDATE_LAST_ROUND = 'NTF_UPDATE_LAST_ROUND';

abstract class UpdateLastRoundActionCommand extends \BX\Action\BaseActionCommand
{
    private static $isLastRoundFct;

    public static function registerIsLastRound(callable $isLastRoundFct)
    {
        self::$isLastRoundFct = $isLastRoundFct;
    }

    public static function getAllDatas($game, array &$result)
    {
        if (self::$isLastRoundFct === null) {
            throw new \BgaSystemException('UpdateLastRoundActionCommand: isLastRoundFct was not registered');
        }
        $stateId = $game->gamestate->state_id();
        $gameHasEnded = ($stateId == STATE_GAME_END_ID);
        $result['isLastRound'] = (!$gameHasEnded && (self::$isLastRoundFct)());
    }

    private $wasLastRound;
    private $showMessage;

    public function __construct(int $playerId, bool $showMessage = true)
    {
        parent::__construct($playerId);
        $this->showMessage = $showMessage;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->wasLastRound = $this->isLastRound();
        if (!$this->wasLastRound && $this->detectLastRound()) {
            $notifier->notify(
                NTF_UPDATE_LAST_ROUND,
                $this->showMessage
                    ? clienttranslate('This is the last round!')
                    : '',
                [
                    'isLastRound' => true,
                ]
            );
            $this->onDetectLastRound($notifier);
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->wasLastRound != null) {
            $notifier->notifyNoMessage(
                NTF_UPDATE_LAST_ROUND,
                [
                    'isLastRound' => $this->wasLastRound,
                ]
            );
        }
    }

    protected function isLastRound()
    {
        if (self::$isLastRoundFct === null) {
            throw new \BgaSystemException('UpdateLastRoundActionCommand: isLastRoundFct was not registered');
        }
        return (self::$isLastRoundFct)();
    }

    protected abstract function detectLastRound();
    protected abstract function onDetectLastRound(\BX\Action\BaseActionCommandNotifier $notifier);
}
