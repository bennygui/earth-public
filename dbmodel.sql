
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- earth implementation : © Guillaume Benny bennygui@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- Cards
CREATE TABLE IF NOT EXISTS `card` (
  -- unique id: Each card in the game will have a unique id
  `card_id` int(10) unsigned NOT NULL,
  -- player that has this card, null if no player has it
  `player_id` int(10) unsigned NULL,
  -- Location of the card: deck, discard, compost, hand, tableau, fauna_board_fauna, fauna_board_ecosystem, box
  `location_id` smallint(5) unsigned NOT NULL,
  -- Order of the card at a location when they are stacked
  `location_order` smallint(5) unsigned NULL,
  -- x of the card for some locations: hand, tableau, fauna_board_fauna
  `location_x` smallint(5) NULL,
  -- y of the card for some locations: tableau, fauna_board_fauna, fauna_board_ecosystem
  `location_y` smallint(5) NULL,
  -- When in the hand of a player, a card can be part of the cards that the player must choose
  `hand_choosing` boolean NOT NULL,
  -- Card can only be viewed by the player in the tableau
  `private_visibility` boolean NOT NULL,
  -- Number of sprouts (cubes) on the card when in the tableau
  `sprout_count` smallint(5) NOT NULL,
  -- Number of growth (thrunks or canopies) on the card when in the tableau
  `growth_count` smallint(5) NOT NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Leaf tokens
CREATE TABLE IF NOT EXISTS `leaf_token` (
  -- unique id: Each leaf token in the game will have a unique id
  `token_id` smallint(5) unsigned NOT NULL,
  -- Leaf token number 0 is used for action selection and tableau bonus and number 1 to 4 are used for fauna bonus
  `leaf_id` smallint(5) unsigned NOT NULL,
  -- player that has this leaf token
  `player_id` int(10) unsigned NOT NULL,
  -- Location of the token: action, player_board, fauna_board_fauna (null location if placed curent turn), fauna_board_tableau_bonus
  `location_id` smallint(5) unsigned NOT NULL,
  -- x of the token for some locations: action, player_board, fauna_board_fauna
  `location_x` smallint(5) NULL,
  -- y of the token for some locations: fauna_board_fauna
  `location_y` smallint(5) NULL,
  -- Order of the token on the fauna_board_fauna for the score. Can be NULL when the final order is not yet known
  `location_order` smallint(5) unsigned NULL,
  -- Same but private for player only
  `private_location_id` smallint(5) unsigned NULL,
  `private_location_x` smallint(5) NULL,
  `private_location_y` smallint(5) NULL,
  `private_location_order` smallint(5) unsigned NULL,
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- State for each players
CREATE TABLE IF NOT EXISTS `player_state` (
  -- The state is for this player
  `player_id` int(10) unsigned NOT NULL,
  -- Number of soil for this player
  `soil_count` smallint(5) NOT NULL,
  -- Number of seeds for this player
  `seed_count` smallint(5) NOT NULL,
  -- Number of sprouts gained in current activation
  `gained_sprout` smallint(5) NOT NULL,
  -- Number of growth gained in current activation
  `gained_growth` smallint(5) NOT NULL,
  -- Number of sprouts to give to one player gained in current activation
  `gained_sprout_choose_one` smallint(5) NOT NULL,
  -- Number cards the player can compost from hand in current activation
  `gained_compost_from_hand` smallint(5) NOT NULL,
  -- Comma separated list of card_id for cards that can be used for gained_sprout and gained_growth
  `gained_card_id_list` varchar(100) NULL,
  -- Are the gains splited for each card in the list of card_id or not
  `gained_card_id_divided` boolean NOT NULL,
  -- Activation direction: player_board_to_tableau, tableau_to_player_board
  `state_activation_direction` smallint(5) NULL,
  -- Current card that is being activated, before any copy ability
  `state_activated_before_copy_card_id` int(10) unsigned NULL,
  -- Current card that is being activated, after any copy ability
  `state_activated_after_copy_card_id` int(10) unsigned NULL,
  -- Copy before event
  `state_event_before_copy_card_id` int(10) unsigned NULL,
  -- Copy before event
  `state_event_after_copy_card_id` int(10) unsigned NULL,
  -- Event card being played
  `state_event_current_card_id` int(10) unsigned NULL,
  -- State to return to when playing an event card
  `return_from_event_state_id` smallint(5) NULL,
  -- State to return to when converting sprouts to soil
  `return_from_conversion_state_id` smallint(5) NULL,
  -- First planted card this current turn
  `first_planted_card_id` int(10) unsigned NULL,
  -- Second planted card this current turn
  `second_planted_card_id` int(10) unsigned NULL,
  -- Third planted card this current turn
  `third_planted_card_id` int(10) unsigned NULL,
  -- Number of sprout on player board at the end of the End Turn phase
  `last_seen_exchange_sprout_count` smallint(5) NULL,
  -- Comma separated list of card_i of end turn event cards in hand for the End Turn phase
  `last_seen_end_turn_event_card_ids` varchar(100) NULL,
  -- Checked if player does not want to be activated for the end of turn phase
  `skip_end_of_turn` boolean NOT NULL,
  -- Stats
  -- Stats: Nb cards drawn in total
  `stat_nb_cards_drawn` int(10) unsigned NULL,
  -- Stats: Nb cards composted in total
  `stat_nb_cards_composted` int(10) unsigned NULL,
  -- Stats: Nb soil gained in total
  `stat_nb_soil_gained` int(10) unsigned NULL,
  -- Stats: Nb cards discarded (paid) from compost
  `stat_nb_cards_paid` int(10) unsigned NULL,
  -- Stats: Nb sprouts placed in total
  `stat_nb_sprouts_placed` int(10) unsigned NULL,
  -- Stats: Nb sprouts discarded (paid) in total
  `stat_nb_sprouts_paid` int(10) unsigned NULL,
  -- Stats: Nb sprouts converted in total
  `stat_nb_sprouts_converted` int(10) unsigned NULL,
  -- Stats: Nb growth placed in total
  `stat_nb_growth_placed` int(10) unsigned NULL,
  -- Stats: Nb growth discarded (paid) in total
  `stat_nb_growth_paid` int(10) unsigned NULL,
  -- Stats: Nb seed gained in total
  `stat_nb_seed_gained` int(10) unsigned NULL,
  -- Stats: Nb leafs converted in total
  `stat_nb_leafs_converted` int(10) unsigned NULL,
  -- Stats: Nb germinate actions
  `stat_nb_germinate` int(10) unsigned NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Exchange for each players
CREATE TABLE IF NOT EXISTS `player_exchange` (
  -- unique id with no meaning 
  `player_exchange_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  -- The player the exchange is from
  `from_player_id` int(10) unsigned NOT NULL,
  -- The player the exchange is to
  `to_player_id` int(10) unsigned NOT NULL,
  -- Number of sprout given
  `sprout_give` smallint(5) NOT NULL,
  -- Number of sprout taken
  `sprout_take` smallint(5) NOT NULL,
  PRIMARY KEY (`player_exchange_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- State for the whole game
CREATE TABLE IF NOT EXISTS `game_state` (
  -- Always 0, there is only one game state
  `game_state_id` smallint(5) NOT NULL,
  -- Current game phase: player_setup, choose_main_action, execute_main_action, card_activation
  `active_game_phase` smallint(5) NOT NULL,
  -- Current main action: plant, compost, water, grow
  `active_main_action_id` smallint(5) NULL,
  -- The player that is the active player for this turn
  `active_player_id` int(10) unsigned NULL,
  -- Is it the last round of the game?
  `is_last_round` boolean NOT NULL,
  -- Number of soil gained by Gaia in solo
  `gaia_soil` smallint(5) NOT NULL,
  -- Number of sprouts gained by Gaia in solo
  `gaia_sprout` smallint(5) NOT NULL,
  -- Number of growth gained by Gaia in solo
  `gaia_growth` smallint(5) NOT NULL,
  -- Color to use for Gaia
  `gaia_color_name` varchar(10) NULL,
  -- Who is "active": 0 for Gaia, 1 and 2 for the player
  `solo_turn` smallint(5) NOT NULL,
  -- Number of soil gained for red action
  `solo_player_gained_soil` smallint(5) NOT NULL,
  -- Number of cards gained for yellow action
  `solo_player_gained_card` smallint(5) NOT NULL,
  -- Number of time the gaia deck was reshuffled
  `gaia_deck_shuffle` smallint(5) NOT NULL,
  -- Last chosen gaia fauna objective was a left objective
  `last_gaia_fauna_left` boolean NOT NULL,
  -- Version of the game
  `game_version` smallint(5) NOT NULL,
  PRIMARY KEY (`game_state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Card tags
CREATE TABLE IF NOT EXISTS `card_tag` (
  -- unique id with no meaning 
  `card_tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  -- The player id for that tag
  `player_id` int(10) unsigned NULL,
  -- The card that is tagged
  `card_id` int(10) unsigned NULL,
  -- The tag
  `card_tag` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`card_tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Player seen fauna objectives
CREATE TABLE IF NOT EXISTS `player_seen_leaf_token` (
  -- unique id with no meaning 
  `player_seen_leaf_token_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  -- The leaf token id
  `token_id` smallint(5) unsigned NOT NULL,
  -- The player id that saw this token on the fauna board
  `player_id` int(10) unsigned NULL,
  PRIMARY KEY (`player_seen_leaf_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- End game card scores
CREATE TABLE IF NOT EXISTS `player_score` (
  -- unique id with no meaning 
  `score_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  -- The player id that scores
  `player_id` int(10) unsigned NULL,
  -- The card that scores: NULL for compost and 4x4 fauna bonus
  `card_id` int(10) unsigned NULL,
  -- The type of score: card score, event score, compost, sprout, growth, terrain (brown), ecosystem, fauna
  `score_type_id` smallint(5) unsigned NOT NULL,
  -- The score
  `score` smallint(5) NOT NULL,
  -- Extra information about the score, like a card id list
  `extra_score` varchar(256) NULL,
  -- Progress for ecosystem cards
  `extra_progress` smallint(5) unsigned NULL,
  PRIMARY KEY (`score_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Actions that are still private to a player and that can be undone
CREATE TABLE IF NOT EXISTS `action_command` (
  -- unique id with no meaning 
  `action_command_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  -- json version of the class
  `action_json` varchar(65535) NOT NULL,
  PRIMARY KEY (`action_command_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;