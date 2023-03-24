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

namespace EA;

require_once('CardDef.php');

trait CardDefMgrClimate
{
    private static function getCardDefClimate()
    {
        return [
            (new CardDefBuilder())->climateId(295, 1)->name(clienttranslate("Arid Climate"))
                ->score(1)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate('Draw 2 cards if you chose the Red Action'))->build())
                ->build(),
            (new CardDefBuilder())->climateId(295, 2)->name(clienttranslate("Humid Subtropical Climate"))
                ->score(4)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->blue()->pay(ABILITY_COMPOST_FROM_HAND, 1)->gain(ABILITY_SPROUT, 1)->build())
                ->build(),
            (new CardDefBuilder())->climateId(296, 1)->name(clienttranslate("Desert Climate"))
                ->score(3)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->yellow()->pay(ABILITY_GROWTH, 1)->gain(ABILITY_SOIL, 2)->build())
                ->build(),
            (new CardDefBuilder())->climateId(296, 2)->name(clienttranslate("Hot Summer Continental Climate"))
                ->score(2)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SOIL, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate('Gain 2 soil if you chose the Red Action'))->build())
                ->build(),
            (new CardDefBuilder())->climateId(297, 1)->name(clienttranslate("Tropical Rainforest Climate"))
                ->score(1)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_BLUE)
                    ->desc(clienttranslate('Draw 2 cards if you chose the Blue Action'))->build())
                ->build(),
            (new CardDefBuilder())->climateId(297, 2)->name(clienttranslate("Dry-Winter Subtropical Highland Climate"))
                ->score(4)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->red()->pay(ABILITY_COMPOST_DESTROY, 1)->gain(ABILITY_SOIL, 2)->build())
                ->build(),
            (new CardDefBuilder())->climateId(298, 1)->name(clienttranslate("Marine West Coast Climate"))
                ->score(1)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->blue()->pay(ABILITY_SPROUT, 1)->gain(ABILITY_GROWTH, 3)->build())
                ->build(),
            (new CardDefBuilder())->climateId(298, 2)->name(clienttranslate("Mediterranean Cold Summer Climate"))
                ->score(4)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_COMPOST_FROM_HAND, 1)->gain(ABILITY_GROWTH, 1)->build())
                ->build(),
            (new CardDefBuilder())->climateId(299, 1)->name(clienttranslate("Boreal Climate"))
                ->score(2)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->blue()->pay(ABILITY_COMPOST_FROM_HAND, 1)->gain(ABILITY_SOIL, 1)->build())
                ->build(),
            (new CardDefBuilder())->climateId(299, 2)->name(clienttranslate("Ice Cap Climate"))
                ->score(3)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->red()->pay(ABILITY_GROWTH, 1)->gain(ABILITY_SOIL, 2)->build())
                ->build(),
            (new CardDefBuilder())->climateId(300, 1)->name(clienttranslate("Subtropical Highland Climate"))
                ->score(2)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->yellow()->pay(ABILITY_COMPOST_DESTROY, 1)->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->build())
                ->build(),
            (new CardDefBuilder())->climateId(300, 2)->name(clienttranslate("Oceanic Climate"))
                ->score(3)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->blue()->pay(ABILITY_GROWTH, 1)->gain(ABILITY_SOIL, 2)->build())
                ->build(),
            (new CardDefBuilder())->climateId(301, 1)->name(clienttranslate("Dry-Winter Subpolar Oceanic Climate"))
                ->score(1)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->yellow()->pay(ABILITY_COMPOST_FROM_HAND, 2)->gain(ABILITY_GROWTH, 1)->build())
                ->build(),
            (new CardDefBuilder())->climateId(301, 2)->name(clienttranslate("Semi-Arid Climate"))
                ->score(4)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_GROWTH, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate('Gain 2 growth if you chose the Red Action'))->build())
                ->build(),
            (new CardDefBuilder())->climateId(302, 1)->name(clienttranslate("Tropical Monsoon Climate"))
                ->score(4)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_GROWTH, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_BLUE)
                    ->desc(clienttranslate('Gain 2 growth if you chose the Blue Action'))->build())
                ->build(),
            (new CardDefBuilder())->climateId(302, 2)->name(clienttranslate("Tundra Climate"))
                ->score(1)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->red()->pay(ABILITY_COMPOST_DESTROY, 1)->gain(ABILITY_SPROUT, 3)->build())
                ->build(),
            (new CardDefBuilder())->climateId(303, 1)->name(clienttranslate("Mediterranean Hot Summer Climate"))
                ->score(2)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_SOIL, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_YELLOW)
                    ->desc(clienttranslate('Gain 2 soil if you chose the Yellow Action'))->build())
                ->build(),
            (new CardDefBuilder())->climateId(303, 2)->name(clienttranslate("Subpolar Oceanic Climate"))
                ->score(3)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_SPROUT, 1)->build())
                ->ability((new AbilityBuilder())->blue()->pay(ABILITY_SPROUT, 1)->gain(ABILITY_COMPOST_FROM_HAND, 3)->build())
                ->build(),
            (new CardDefBuilder())->climateId(304, 1)->name(clienttranslate("Tropical Savanna Climate"))
                ->score(1)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->yellow()->pay(ABILITY_COMPOST_FROM_HAND, 1)->gain(ABILITY_SPROUT, 3)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_YELLOW)
                    ->desc(clienttranslate('Pay by composting 1 card from your hand to gain 3 sprout, if you chose the Yellow Action'))->build())
                ->build(),
            (new CardDefBuilder())->climateId(304, 2)->name(clienttranslate("Hemiboreal Climate"))
                ->score(4)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_SOIL, 1)->build())
                ->build(),
        ];
    }
}
