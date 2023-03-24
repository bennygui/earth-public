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

trait CardDefMgrEcosystem
{
    private static function getCardDefEcosystem()
    {
        return [
            (new CardDefBuilder())->ecosystemId(305, 1)->name(clienttranslate("Blackforest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_TOTAL_CARD_TYPE, CARD_TYPE_TREE, 0, 4, 8, 13, 23, 28, 30])
                    ->desc(clienttranslate('Earn points depending on the total number of Tree in your island (1-7+): 0, 4, 8, 13, 23, 28, or 30'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(305, 2)->name(clienttranslate("Daintree Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIAG_LINE_CARD_TYPE, CARD_TYPE_TREE, 4, 7])
                    ->desc(clienttranslate('Score 4 points per Tree in one continuous line through diagonally adjacent cards (1-7. Line may turn, not backtrack)'))
                    ->faq(clienttranslate("Draw a line that can turn up-left, up-right, down-left, down-right and that passes only through cards of the matching type. You cannot pass through the same card twice. Try to make that line as long as possible. Score 4 points per card it passes through."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(306, 1)->name(clienttranslate("Great Steppe Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_TOTAL_CARD_TYPE, CARD_TYPE_BUSH, 0, 4, 8, 13, 23, 28, 30])
                    ->desc(clienttranslate('Earn points depending on the total number of Bush in your island (1-7+): 0, 4, 8, 13, 23, 28, or 30'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(306, 2)->name(clienttranslate("Florida Scrub Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIAG_LINE_CARD_TYPE, CARD_TYPE_BUSH, 4, 7])
                    ->desc(clienttranslate('Score 4 points per Bush in one continuous line through diagonally adjacent cards (1-7. Line may turn, not backtrack)'))
                    ->faq(clienttranslate("Draw a line that can turn up-left, up-right, down-left, down-right and that passes only through cards of the matching type. You cannot pass through the same card twice. Try to make that line as long as possible. Score 4 points per card it passes through."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(307, 1)->name(clienttranslate("Rocky Mountains Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_HABITAT, 3, 2, CARD_HABITAT_ROCKY])
                    ->desc(clienttranslate('Score 3 points per 2 Rocky habitat (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(307, 2)->name(clienttranslate("Nile Delta Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_HABITAT, 3, 2, CARD_HABITAT_WET])
                    ->desc(clienttranslate('Score 3 points per 2 Wet habitat (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(308, 1)->name(clienttranslate("Namib Desert Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_HABITAT, 3, 2, CARD_HABITAT_SUNNY])
                    ->desc(clienttranslate('Score 3 points per 2 Sunny habitat (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(308, 2)->name(clienttranslate("Denali National Park Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_HABITAT, 3, 2, CARD_HABITAT_COLD])
                    ->desc(clienttranslate('Score 3 points per 2 Cold habitat (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(309, 1)->name(clienttranslate("Antarctica Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_ABILITY_COLOR, 9, 2, AB_COLOR_BLACK])
                    ->desc(clienttranslate('Score 9 points per 2 Black abilities (includes Island)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(309, 2)->name(clienttranslate("The Great Plains Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_REMAINING_SOIL, 2, 24])
                    ->desc(clienttranslate('Score 2 points per soil in your reserve (maximum 24 points)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(310, 1)->name(clienttranslate("Borneo Lowland Rain Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_REMAINING_CARD_IN_HAND, 6, 6, 24])
                    ->desc(clienttranslate('Score 6 points per 6 cards in your hand (maximum 24 points)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(310, 2)->name(clienttranslate("Amazon Rain Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARD_IN_COMPOST, 3, 4, 24])
                    ->desc(clienttranslate('Score 3 points per 4 cards in your compost (maximum 24 points)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(311, 1)->name(clienttranslate("MacMillan Park Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_MORE_COST, 5, 2, 4])
                    ->desc(clienttranslate('Score 5 points per 2 cards, each costing 4 soil or more'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(311, 2)->name(clienttranslate("Great Basin Desert Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_LESS_COST, 3, 2, 3])
                    ->desc(clienttranslate('Score 3 points per 2 cards, each costing 3 soil or less'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(312, 1)->name(clienttranslate("Congolian Rainforests Ecosytem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_MORE_SPROUTS, 5, 2, 4])
                    ->desc(clienttranslate('Score 5 points per 2 cards, each with 4 sprouts or more'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(312, 2)->name(clienttranslate("Redwood National Park Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_MORE_GROWTH, 3, 3])
                    ->desc(clienttranslate('Score 3 points per Flora with 3 growth or more'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(313, 1)->name(clienttranslate("Yellowstone Caldera Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_ABILITY_COLOR, 2, 1, AB_COLOR_RED])
                    ->desc(clienttranslate('Score 2 points per Red ability (multicolor count)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(313, 2)->name(clienttranslate("Angat Watershed Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_EVENT, 5, 2, 25])
                    ->desc(clienttranslate('Score 5 points per 2 event cards in your Event Stack (maximum 25 points)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(314, 1)->name(clienttranslate("Serengeti Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_ABILITY_COLOR, 2, 1, AB_COLOR_YELLOW])
                    ->desc(clienttranslate('Score 2 points per Yellow ability (multicolor count)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(314, 2)->name(clienttranslate("Okavango Delta Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_ABILITY_COLOR, 2, 1, AB_COLOR_BLUE])
                    ->desc(clienttranslate('Score 2 points per Blue ability (multicolor count)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(315, 1)->name(clienttranslate("Ngorongoro Crater Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_ABILITY_COLOR, 6, AB_DIRECTION_ROW])
                    ->desc(clienttranslate('Score 6 points per row if all 4 cards have a different ability colour'))
                    ->faq(clienttranslate("You need 4 cards in your row/column and you need to be able to pick one color on each card and then have 4 different colors. If you have those 4 cards: Green|Brown, Yellow, Red|Blue, Red|Blue, you can pick Green, Yellow, Red, and Blue (or Green, Yellow, Blue, and Red) so this row would score. Green, Red, Blue, Yellow, Brown and Black are all ability colors."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(315, 2)->name(clienttranslate("Waiotapu Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_SAME_ABILITY_COLOR, 6, AB_DIRECTION_ROW])
                    ->desc(clienttranslate('Score 6 points per row if all 4 cards share one ability colour'))
                    ->faq(clienttranslate("You need 4 cards in your row/column and you must be able to pick one color that all 4 cards have."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(316, 1)->name(clienttranslate("Mauna Kea Ecosystems"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_ABILITY_COLOR, 6, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 6 points per column if all 4 cards have a different ability colour'))
                    ->faq(clienttranslate("You need 4 cards in your row/column and you need to be able to pick one color on each card and then have 4 different colors. If you have those 4 cards: Green|Brown, Yellow, Red|Blue, Red|Blue, you can pick Green, Yellow, Red, and Blue (or Green, Yellow, Blue, and Red) so this row would score. Green, Red, Blue, Yellow, Brown and Black are all ability colors."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(316, 2)->name(clienttranslate("Aconcagua Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_SAME_ABILITY_COLOR, 6, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 6 points per column if all 4 cards share one ability colour'))
                    ->faq(clienttranslate("You need 4 cards in your row/column and you must be able to pick one color that all 4 cards have."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(317, 1)->name(clienttranslate("New Guinea Rain Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_MORE_SCORE, 5, 2, 4])
                    ->desc(clienttranslate('Score 5 points per 2 cards, each with a score value of 4 or more'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(317, 2)->name(clienttranslate("Atacama Desert Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_LESS_SCORE, 12, 8, 3])
                    ->desc(clienttranslate('Score 12 points per 8 cards, each with a score value of 3 or less'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(318, 1)->name(clienttranslate("Odisha Semi-Evergreen Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_DIRECTIONS, 7, 2])
                    ->desc(clienttranslate('Score 7 points per 2 cards, each with directional aids'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(318, 2)->name(clienttranslate("Irati Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_SCORE, 6, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 6 points per column in which each of the 4 cards have a different score value'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(319, 1)->name(clienttranslate("Monteverde Cloud Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_TOTAL_CARD_TYPE, CARD_TYPE_MUSHROOM, 0, 4, 8, 13, 23, 28, 30])
                    ->desc(clienttranslate('Earn points depending on the total number of Mushroom in your island (1-7+): 0, 4, 8, 13, 23, 28, or 30'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(319, 2)->name(clienttranslate("Everglades Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIAG_LINE_CARD_TYPE, CARD_TYPE_MUSHROOM, 4, 7])
                    ->desc(clienttranslate('Score 4 points per Mushroom in one continuous line through diagonally adjacent cards (1-7. Line may turn, not backtrack)'))
                    ->faq(clienttranslate("Draw a line that can turn up-left, up-right, down-left, down-right and that passes only through cards of the matching type. You cannot pass through the same card twice. Try to make that line as long as possible. Score 4 points per card it passes through."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(320, 1)->name(clienttranslate("Great Hungarian Plain Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_TOTAL_CARD_TYPE, CARD_TYPE_HERB, 0, 4, 8, 13, 23, 28, 30])
                    ->desc(clienttranslate('Earn points depending on the total number of Herb in your island (1-7+): 0, 4, 8, 13, 23, 28, or 30'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(320, 2)->name(clienttranslate("Caerlaverock Nature Reserve Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIAG_LINE_CARD_TYPE, CARD_TYPE_HERB, 4, 7])
                    ->desc(clienttranslate('Score 4 points per Herb in one continuous line through diagonally adjacent cards (1-7. Line may turn, not backtrack)'))
                    ->faq(clienttranslate("Draw a line that can turn up-left, up-right, down-left, down-right and that passes only through cards of the matching type. You cannot pass through the same card twice. Try to make that line as long as possible. Score 4 points per card it passes through."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(321, 1)->name(clienttranslate("Valin Moutain Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_MORE_HABITAT, 4, 3, 2])
                    ->desc(clienttranslate('Score 4 points per 3 cards with 2 or more Habitats (including Island and Climate) (Sunny, Wet, Rocky and/or Cold)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(321, 2)->name(clienttranslate("Bashkiriya National Park Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_CARDS_WITH_LESS_HABITAT, 4, 2, 1])
                    ->desc(clienttranslate('Score 4 points per 2 cards with 1 Habitat or less (including Island and Climate) (Sunny, Wet, Rocky and/or Cold)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(322, 1)->name(clienttranslate("Yuanjiang Savanna Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_SPROUT_COUNT, 6, AB_DIRECTION_ROW])
                    ->desc(clienttranslate('Score 6 points per row in which each of the 4 cards have a different number of sprouts (0-6)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(322, 2)->name(clienttranslate("Sierra Nevada De Santa Marta Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_SPROUT_COUNT, 6, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 6 points per column in which each of the 4 cards have a different number of sprouts (0-6)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(323, 1)->name(clienttranslate("Alps Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_GROWTH_COUNT, 7, AB_DIRECTION_ROW])
                    ->desc(clienttranslate('Score 7 points per row in which each of the 4 cards have a different number of growth (0-6)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(323, 2)->name(clienttranslate("Himalayas Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_GROWTH_COUNT, 7, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 7 points per column in which each of the 4 cards have a different number of growth (0-6)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(324, 1)->name(clienttranslate("Tai Poutini National Park Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_SPROUT, 3, 5])
                    ->desc(clienttranslate('Score 3 points per 5 sprouts on your island (tableau)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(324, 2)->name(clienttranslate("Madagascar Humid Canopy Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_GROWTH, 2, 3])
                    ->desc(clienttranslate('Score 2 points per 3 growth on your island (tableau)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(325, 1)->name(clienttranslate("Sakurajima Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARDS_SETS, 6])
                    ->desc(clienttranslate('Score 6 points per set of 5 cards including Yellow, Blue, Red, Terrain and Event, each on a different card (including Island and Climate)'))
                    ->faq(clienttranslate("To get 12 points with this card, you must have 2 Red, 2 Blue, 2 Yellow, 2 Terrains and 2 Events. Each must be from a different card."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(325, 2)->name(clienttranslate("Réunion Island Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARDS_WITH_TWO_ABILITIES, 5, 2])
                    ->desc(clienttranslate('Score 5 points per 2 cards, each having 2 abilities (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(326, 1)->name(clienttranslate("Chic-Choc Mountains Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_CARD_TYPE, 6, AB_DIRECTION_ROW])
                    ->desc(clienttranslate('Score 6 points per row with 4 cards, each of a different type: Tree, Bush, Mushroom, Herb, Terrain'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(326, 2)->name(clienttranslate("Finland Snow Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_SAME_CARD_TYPE, 6, AB_DIRECTION_ROW])
                    ->desc(clienttranslate('Score 6 points per row with 4 cards, all of the same type: Tree, Bush, Mushroom, Herb, Terrain'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(327, 1)->name(clienttranslate("Mbeliling Mountain Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_CARD_TYPE, 6, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 6 points per column with 4 cards, each of a different type: Tree, Bush, Mushroom, Herb, Terrain'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(327, 2)->name(clienttranslate("Atlas Mountains Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_SAME_CARD_TYPE, 6, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 6 points per column with 4 cards, all of the same type: Tree, Bush, Mushroom, Herb, Terrain'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(328, 1)->name(clienttranslate("Sudd Swamp Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_DIFFERENT_HABITAT, 6, AB_DIRECTION_ROW])
                    ->desc(clienttranslate('Score 6 points per row of 4 cards, each with at least one different Habitat: Sunny, Wet, Rocky or Cold'))
                    ->faq(clienttranslate("A row scores if every card on the row has at least 1 habitat and if the 4 different habitats are on 4 different cards. You need the 4 different habitats in the row, each being on a different card. If a card has different habitats, the player chooses the one that fits the best, regarding the other cards."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(328, 2)->name(clienttranslate("Thai Highlands Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_DIRECTION_WITH_SAME_HABITAT, 6, AB_DIRECTION_COLUMN])
                    ->desc(clienttranslate('Score 6 points per column of 4 cards sharing at least one common Habitat: Sunny, Wet, Rocky or Cold'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(329, 1)->name(clienttranslate("Tasmanian Temperate Rain Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_BOLD_GEOGRAPHY, 9, 2])
                    ->desc(clienttranslate('Score 9 points per 2 Flora, each with a geographic term in their name (terms are in bold)'))
                    ->faq(clienttranslate("Some compromise had to be made for balance issues so not all geographic term may be bold. Only bold terms can be counted toward this objective."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(329, 2)->name(clienttranslate("Batanta Island Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_ITALIC_COLOR, 9, 2])
                    ->desc(clienttranslate('Score 9 points per 2 Flora, each with a color in their name (colors are in italic)'))
                    ->faq(clienttranslate("Some compromise had to be made for balance issues so not all colors may be italic. Only italic colors can be counted toward this objective."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(330, 1)->name(clienttranslate("Knysna-Amatole Forests Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_UNDERLINE_ANIMAL, 9, 2])
                    ->desc(clienttranslate('Score 9 points per 2 Flora, each with an animal in their name (animals are underlined)'))
                    ->faq(clienttranslate("Some compromise had to be made for balance issues so not all animals may be underlined. Only underlined animals can be counted toward this objective."))
                    ->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(330, 2)->name(clienttranslate("Jiuzhaigou Valley Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_ABILITY_COLOR, 11, 2, AB_COLOR_MULTICOLOR])
                    ->desc(clienttranslate('Score 11 points per 2 multicolor abilities'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(331, 1)->name(clienttranslate("Australian Temperate Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_EXACT_PIECE_SPOT, 7, 2, 6])
                    ->desc(clienttranslate('Score 7 points per 2 Flora, each with 6 sprout spaces (sprout are not needed)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(331, 2)->name(clienttranslate("Yagishiri Island Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_LESS_PIECE_SPOT, 5, 2, 3, ABILITY_SPROUT])
                    ->desc(clienttranslate('Score 5 points per 2 Flora, each with 3 or less sprout spaces (sprout are not needed)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(332, 1)->name(clienttranslate("Bhutan Rain Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_MORE_PIECE_SPOT, 8, 2, 4, ABILITY_GROWTH])
                    ->desc(clienttranslate('Score 8 points per 2 Flora, each with 4 or more growth spaces (growth are not needed)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(332, 2)->name(clienttranslate("Arabian Desert Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_LESS_PIECE_SPOT, 4, 2, 2, ABILITY_GROWTH])
                    ->desc(clienttranslate('Score 4 points per 2 Flora, each with 2 or less growth spaces (growth are not needed)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(333, 1)->name(clienttranslate("Twin Islands Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_EVEN_SCORE, 4, 3])
                    ->desc(clienttranslate('Score 4 points per 3 cards with an even score value (0 counts as even) (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(333, 2)->name(clienttranslate("Lonely Island Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_ODD_SCORE, 4, 2])
                    ->desc(clienttranslate('Score 4 points per 2 cards with an odd score value (0 counts as even) (including Island and Climate)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(334, 1)->name(clienttranslate("Tangkoko Nature Reserve Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_MORE_GROWTH_MAX_SCORE, 6, 2, 5])
                    ->desc(clienttranslate('Score 6 points per 2 cards, each with a growth space value of 5 or more points'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(334, 2)->name(clienttranslate("Siberian Taiga Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_LESS_GROWTH_MAX_SCORE, 2, 1, 4])
                    ->desc(clienttranslate('Score 2 points per card, each with a growth space value of 4 or less points'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(335, 1)->name(clienttranslate("Tongass National Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_FILLED_FIECES, 2, ABILITY_SPROUT])
                    ->desc(clienttranslate('Score 2 points per Flora with a sprout on every sprout space (1-6)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(335, 2)->name(clienttranslate("Bwindi Impenetrable Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_CARD_WITH_FILLED_FIECES, 2, ABILITY_GROWTH])
                    ->desc(clienttranslate('Score 2 points per Canopy (filled growth space)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(336, 1)->name(clienttranslate("Mount Kilimanjaro Ecosystems"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_ABILITY_COLOR, 5, 2, AB_COLOR_BROWN])
                    ->desc(clienttranslate('Score 5 points per 2 Brown abilities (includes Island)'))->build())
                ->build(),
            (new CardDefBuilder())->ecosystemId(336, 2)->name(clienttranslate("Tropical Evergreen Forest Ecosystem"))
                ->ability((new AbilityBuilder())->scores([AB_ECO_PER_ABILITY_COLOR, 10, 2, AB_COLOR_GREEN])
                    ->desc(clienttranslate('Score 10 points per 2 Green abilities (includes Island)'))->build())
                ->build(),
        ];
    }
}
