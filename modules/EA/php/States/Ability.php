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

namespace EA\State\Ability;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');

trait GameStatesTrait
{
    public function argsAbilityGain(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            $ps = $playerStateMgr->getByPlayerId($playerId);
            $logs = [];
            $args = [];
            $hasGain = false;
            if ($ps->gainedSprout > 0) {
                $hasGain = true;
                $logs[] = '${gainedSprout} ${sproutIcon}';
                $args['gainedSprout'] = $ps->gainedSprout;
                $args['sproutIcon'] = clienttranslate('sprout(s)');
                $args['i18n'][] = 'sproutIcon';
            }
            if ($ps->gainedGrowth > 0) {
                $hasGain = true;
                $logs[] = '${gainedGrowth} ${growthIcon}';
                $args['gainedGrowth'] = $ps->gainedGrowth;
                $args['growthIcon'] = clienttranslate('growth(s)');
                $args['i18n'][] = 'growthIcon';
            }
            if ($ps->gainedCompostFromHand > 0) {
                $hasGain = true;
                $logs[] = '${gainedCompostFromHand} ${compostFromHandIcon}';
                $args['gainedCompostFromHand'] = $ps->gainedCompostFromHand;
                $args['compostFromHandIcon'] = clienttranslate('compost');
                $args['i18n'][] = 'compostFromHandIcon';
            }
            $handChoosingCards = $cardMgr->getPlayerHandChoosingCards($playerId);
            if (count($handChoosingCards) > 0) {
                $hasGain = true;
                $logs[] = clienttranslate('1 of ${nbHandChoosingCards} cards');
                $args['nbHandChoosingCards'] = count($handChoosingCards);
            }
            if (!$hasGain) {
                $logs[] = clienttranslate('(nothing)');
            }
            $ret = [
                'gainList' => [
                    'log' => implode(', ', $logs),
                    'args' => $args,
                ],
                'gainedSprout' => $ps->gainedSprout,
                'sproutCards' => $cardMgr->getPlayerTableauCountSprout($playerId),
                'gainedGrowth' => $ps->gainedGrowth,
                'growthCards' => $cardMgr->getPlayerTableauCountGrowth($playerId),
                'gainedCardIdList' => $ps->getGainedCardIdList(),
                'isGainedCardIdListDivided' => $ps->isGainedCardIdListDivided(),
                'gainedCompostFromHand' => $ps->gainedCompostFromHand,
                'compostFromHandCardIds' => array_keys($cardMgr->getPlayerHandCards($playerId)),
                'activatedBeforeCopyCardId' => $playerStateMgr->stateActivatedBeforeCopyCardId($playerId),
                'activatedAfterCopyCardId' => $playerStateMgr->stateActivatedAfterCopyCardId($playerId),
                'handChoosingCardIds' => array_keys($handChoosingCards),
                'mainActionId' => $gameStateMgr->getActiveMainActionId(),
            ];
            return $this->argsMergeEarthBasic($ret);
        });
    }

    public function argsAbilityPayment(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
            $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
            if ($cardId === null) {
                return $this->argsMergeEarthDefault($playerId, []);
            }
            $mainActionId = $gameStateMgr->getActiveMainActionId();
            $cardDef = \EA\CardDefMgr::getByCardId($cardId);
            $ability = null;
            if ($mainActionId === null || $cardDef->isEvent()) {
                $ability = $cardDef->abilityBlack();
            } else {
                $ability = $cardDef->getAbilityMatchingMainAction($mainActionId);
            }
            if ($ability === null) {
                return $this->argsMergeEarthDefault($playerId, []);
            }

            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $ret = [
                'sproutCount' => 0,
                'sproutCards' => $cardMgr->getPlayerTableauCountSprout($playerId),
                'growthCount' => 0,
                'growthCards' => $cardMgr->getPlayerTableauCountGrowth($playerId),
                'compostFromHandCount' => 0,
                'handCardIds' => array_keys($cardMgr->getPlayerHandCards($playerId)),
                'activatedBeforeCopyCardId' => $playerStateMgr->stateActivatedBeforeCopyCardId($playerId),
                'activatedAfterCopyCardId' => $playerStateMgr->stateActivatedAfterCopyCardId($playerId),
                'mainActionId' => $mainActionId,
            ];
            $logs = [];
            $args = [];
            $ability->foreachPayment(function ($abilityId, $count) use (&$ret, &$logs, &$args) {
                switch ($abilityId) {
                    case \EA\ABILITY_GROWTH:
                        $ret['growthCount'] = $count;
                        if ($count > 0) {
                            $logs[] = '${payGrowth} ${growthIcon}';
                            $args['payGrowth'] = $count;
                            $args['growthIcon'] = clienttranslate('growth(s)');
                            $args['i18n'][] = 'growthIcon';
                        }
                        break;
                    case \EA\ABILITY_SPROUT:
                        $ret['sproutCount'] = $count;
                        if ($count > 0) {
                            $logs[] = '${paySprout} ${sproutIcon}';
                            $args['paySprout'] = $count;
                            $args['sproutIcon'] = clienttranslate('sprout(s)');
                            $args['i18n'][] = 'sproutIcon';
                        }
                        break;
                    case \EA\ABILITY_COMPOST_FROM_HAND:
                        $ret['compostFromHandCount'] = $count;
                        if ($count > 0) {
                            $logs[] = '${payCompostFromHand} ${compostFromHandIcon}';
                            $args['payCompostFromHand'] = $count;
                            $args['compostFromHandIcon'] = clienttranslate('compost');
                            $args['i18n'][] = 'compostFromHandIcon';
                        }
                        break;
                }
            });
            $ret['payList'] = [
                'log' => implode(', ', $logs),
                'args' => $args,
            ];
            // Disable playing events in payment
            $ret['canPlayEvent'] = false;
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }
}
