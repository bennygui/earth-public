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

trait CardDefMgrIsland
{
    private static function getCardDefIsland()
    {
        return [
            // 285
            (new CardDefBuilder())->islandId(285, 1)->name(clienttranslate("Kauai Island"))
                ->score(0)
                ->wet()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 12)->pay(ABILITY_COMPOST_FROM_HAND, 5)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_DRAW_CARD_FROM_DECK, 1)->condition(AB_COND_PER_TYPE, CARD_TYPE_TERRAIN)
                    ->desc(clienttranslate('Draw 1 card per terrain card you planted this turn'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(285, 2)->name(clienttranslate("Vulcano Island"))
                ->score(10)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 6)->pay(ABILITY_COMPOST_FROM_HAND, 3)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_SOIL, 1)->condition(AB_COND_WHEN_PLAYING_EVENT)
                    ->desc(clienttranslate('Gain 1 soil each time you play an event card'))->build())
                ->build(),
            // 286
            (new CardDefBuilder())->islandId(286, 1)->name(clienttranslate("Nishinoshima Island"))
                ->score(0)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 11)->pay(ABILITY_COMPOST_FROM_HAND, 3)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_REDUCE_COST_FOR_HABITAT, 1)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_SUNNY)
                    ->desc(clienttranslate('Sunny habitat cost one less soil (not less than 0)'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(286, 2)->name(clienttranslate("Luzon Island"))
                ->score(10)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 6)->pay(ABILITY_COMPOST_FROM_HAND, 4)->gain(ABILITY_SOIL, 4)->build())
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_SPROUT, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_YELLOW)
                    ->desc(clienttranslate('Gain 2 sprouts if you chose the Yellow Action this turn'))->build())
                ->build(),
            // 287
            (new CardDefBuilder())->islandId(287, 1)->name(clienttranslate("Deception Island"))
                ->score(1)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 10)->pay(ABILITY_COMPOST_FROM_HAND, 4)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_REDUCE_COST_FOR_HABITAT, 1)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_COLD)
                    ->desc(clienttranslate('Cold habitat cost one less soil (not less than 0)'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(287, 2)->name(clienttranslate("Nisyros Island"))
                ->score(9)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 3)->pay(ABILITY_COMPOST_FROM_HAND, 0)->gain(ABILITY_SOIL, 4)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_GROWTH, 2)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_SUNNY)
                    ->desc(clienttranslate('Gain 2 growth per card with Sunny habitat you planted this turn'))->build())
                ->build(),
            // 288
            (new CardDefBuilder())->islandId(288, 1)->name(clienttranslate("Barren Island"))
                ->score(1)
                ->wet()->rocky()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 10)->pay(ABILITY_COMPOST_FROM_HAND, 2)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_COMPOST_FROM_HAND, 3)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate('Compost up to 3 cards from your hand if you chose the Red Action this turn'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(288, 2)->name(clienttranslate("Santorini Island"))
                ->score(9)
                ->sunny()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 4)->pay(ABILITY_COMPOST_FROM_HAND, 0)->gain(ABILITY_SOIL, 4)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SOIL, 2)->condition(AB_COND_PER_COLOR, AB_COLOR_YELLOW)
                    ->desc(clienttranslate('Gain 2 soil per card with a Yellow ability you planted this turn'))->build())
                ->build(),
            // 289
            (new CardDefBuilder())->islandId(289, 1)->name(clienttranslate("Mo'orea Island"))
                ->score(4)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 8)->pay(ABILITY_COMPOST_FROM_HAND, 5)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->yellow()->gain(ABILITY_GROWTH, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_YELLOW)
                    ->desc(clienttranslate('Gain 2 growth if you chose the Yellow Action this turn'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(289, 2)->name(clienttranslate("Iceland Island"))
                ->score(6)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 5)->pay(ABILITY_COMPOST_FROM_HAND, 1)->gain(ABILITY_SOIL, 4)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SOIL, 2)->condition(AB_COND_PER_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate('Gain 2 soil per card with a Red ability you planted this turn'))->build())
                ->build(),
            // 290
            (new CardDefBuilder())->islandId(290, 1)->name(clienttranslate("Kunashir Island"))
                ->score(2)
                ->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 8)->pay(ABILITY_COMPOST_FROM_HAND, 2)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SPROUT, 2)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_COLD)
                    ->desc(clienttranslate('Gain 2 sprouts per card with Cold habitat you planted this turn'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(290, 2)->name(clienttranslate("Jan Mayen Island"))
                ->score(8)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 5)->pay(ABILITY_COMPOST_FROM_HAND, 2)->gain(ABILITY_SOIL, 5)->build())
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_SOIL, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_BLUE)
                    ->desc(clienttranslate('Gain 2 soil if you chose the Blue Action this turn'))->build())
                ->build(),
            // 291
            (new CardDefBuilder())->islandId(291, 1)->name(clienttranslate("Kyushu Island"))
                ->score(2)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 8)->pay(ABILITY_COMPOST_FROM_HAND, 3)->gain(ABILITY_SOIL, 8)->build())
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_REDUCE_COST_FOR_HABITAT, 1)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_WET)
                    ->desc(clienttranslate('Wet habitat cost one less soil (not less than 0)'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(291, 2)->name(clienttranslate("Jamaica Island"))
                ->score(8)
                ->sunny()->wet()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 6)->pay(ABILITY_COMPOST_FROM_HAND, 3)->gain(ABILITY_SOIL, 5)->build())
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_DRAW_CARD_FROM_DECK, 1)->condition(AB_COND_WHEN_PLAYING_EVENT)
                    ->desc(clienttranslate('Draw 1 card each time you play an event card'))->build())
                ->build(),
            // 292
            (new CardDefBuilder())->islandId(292, 1)->name(clienttranslate("La Palma Island"))
                ->score(5)
                ->rocky()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 6)->pay(ABILITY_COMPOST_FROM_HAND, 2)->gain(ABILITY_SOIL, 7)->build())
                ->ability((new AbilityBuilder())->blue()->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_BLUE)
                    ->desc(clienttranslate('Draw 2 cards if you chose the Blue Action this turn'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(292, 2)->name(clienttranslate("Metis Shoal Island"))
                ->score(5)
                ->sunny()->rocky()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 7)->pay(ABILITY_COMPOST_FROM_HAND, 4)->gain(ABILITY_SOIL, 4)->build())
                ->ability((new AbilityBuilder())->red()->gain(ABILITY_DRAW_CARD_FROM_DECK, 2)->condition(AB_COND_IF_CHOOSE_COLOR, AB_COLOR_RED)
                    ->desc(clienttranslate('Draw 2 cards if you chose the Red Action this turn'))->build())
                ->build(),
            // 293
            (new CardDefBuilder())->islandId(293, 1)->name(clienttranslate("Whakaari Island"))
                ->score(3)
                ->wet()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 7)->pay(ABILITY_COMPOST_FROM_HAND, 4)->gain(ABILITY_SOIL, 5)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SOIL, 2)->condition(AB_COND_PER_TYPE, CARD_TYPE_TERRAIN)
                    ->desc(clienttranslate('Gain 2 soil per terrain card you planted this turn'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(293, 2)->name(clienttranslate("Fogo Island"))
                ->score(7)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 5)->pay(ABILITY_COMPOST_FROM_HAND, 3)->gain(ABILITY_SOIL, 6)->build())
                ->ability((new AbilityBuilder())->brown()->gain(ABILITY_REDUCE_COST_FOR_HABITAT, 1)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_ROCKY)
                    ->desc(clienttranslate('Rocky habitat cost 1 less soil (not less than 0)'))->build())
                ->build(),
            // 294
            (new CardDefBuilder())->islandId(294, 1)->name(clienttranslate("Hawai'i Island"))
                ->score(4)
                ->rocky()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 5)->pay(ABILITY_COMPOST_FROM_HAND, 2)->gain(ABILITY_SOIL, 7)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_SPROUT, 2)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_ROCKY)
                    ->desc(clienttranslate('Gain 2 sprouts for each card with Rocky habitat you planted this turn'))->build())
                ->build(),
            (new CardDefBuilder())->islandId(294, 2)->name(clienttranslate("Lombok Island"))
                ->score(6)
                ->sunny()->cold()
                ->ability((new AbilityBuilder())->black()->gain(ABILITY_DRAW_CARD_FROM_DECK, 5)->pay(ABILITY_COMPOST_FROM_HAND, 1)->gain(ABILITY_SOIL, 4)->build())
                ->ability((new AbilityBuilder())->green()->gain(ABILITY_GROWTH, 2)->condition(AB_COND_PER_HABITAT, CARD_HABITAT_WET)
                    ->desc(clienttranslate('Gain 2 growth for each card with Wet habitat you planted this turn'))->build())
                ->build(),
        ];
    }
}
