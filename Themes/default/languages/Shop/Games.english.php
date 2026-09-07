<?php

/**
 * @package ST Shop
 * @version 4.0
 * @author Diego Andrés <diegoandres_cortes@outlook.com>
 * @copyright Copyright (c) 2020, SMF Tricks
 * @license https://www.mozilla.org/en-US/MPL/2.0/
 */

global  $scripturl, $txt, $modSettings;

// Games
$txt['Shop_games_slots'] = 'Slots';
$txt['Shop_games_lucky2'] = 'Lucky2';
$txt['Shop_games_number'] = 'Number Slots';
$txt['Shop_games_pairs'] = 'Pairs';
$txt['Shop_games_dice'] = 'Dice';
$txt['Shop_games_bet'] = 'Bet';
$txt['Shop_games_seven'] = 'Seven';
$txt['Shop_games_blackjack'] = 'Blackjack';

// Games Room
$txt['Shop_games_welcome'] = 'Welcome to the Games Room';
$txt['Shop_games_welcome_desc'] = 'Here you can play some games and win some money, or lose it... Good luck and enjoy!<br />You currently have %d days left to play in the Games Room.';
$txt['Shop_games_list'] = 'List of games available';
$txt['Shop_games_playgame'] = 'Play the game!';
$txt['Shop_games_letsplay'] = 'Let\'s play ';
$txt['Shop_games_payouts'] = 'Payouts table';
$txt['Shop_games_loser'] = 'Sorry, you lost %s';
$txt['Shop_games_winner'] = 'Congratulations, you won %s';
$txt['Shop_games_spin'] = 'Spin the wheel';
$txt['Shop_games_roll'] = 'Roll the dice';
$txt['Shop_games_deal'] = 'Deal the cards';
$txt['Shop_games_again'] = 'Try again!';
$txt['Shop_games_youhave'] = 'You have: ';
$txt['Shop_games_slots_desc'] = 'On this game you have to try to get exactly the same three images. Good luck!';
$txt['Shop_games_lucky2_desc'] = 'Here you just have to roll the dice and try to get a <strong>two</strong>. Good luck!';
$txt['Shop_games_number_desc'] = 'This game it\'s very similar to Slots, the difference is that here you have only numbers, and better chances to win. Good luck!';
$txt['Shop_games_number_complete'] = 'Three numbers: ';
$txt['Shop_games_number_firsttwo'] = 'First two numbers: ';
$txt['Shop_games_number_secondtwo'] = 'Last two numbers: ';
$txt['Shop_games_number_firstlast'] = 'First and last numbers: ';
$txt['Shop_games_pairs_desc'] = 'This game is quite hard, you need to get the exact pair of cards, and there are 52. Good luck!';
$txt['Shop_games_dice_desc'] = 'On this game you need to get the same numbers in both dices, let\'s roll the dice. Good luck!';

// Games settings
$txt['Shop_settings_slots_desc'] = 'Here you can define how much the user will win or lose in Slots';
$txt['Shop_settings_slots_losing'] = 'How much the user will lose';
$txt['Shop_settings_slots_7'] = '7\'s payout';
$txt['Shop_settings_slots_bell'] = 'Bells payout';
$txt['Shop_settings_slots_cherry'] = 'Cherries payout';
$txt['Shop_settings_slots_lemon'] = 'Lemons payout';
$txt['Shop_settings_slots_orange'] = 'Oranges payout';
$txt['Shop_settings_slots_plum'] = 'Plums payout';
$txt['Shop_settings_slots_dollar'] = 'Dollars payout';
$txt['Shop_settings_slots_melon'] = 'Melons payout';
$txt['Shop_settings_slots_grapes'] = 'Grapes payout';
$txt['Shop_settings_lucky2_desc'] = 'Here you can define how much the user will win or lose in Lucky2';
$txt['Shop_settings_lucky2_losing'] = 'How much the user will lose';
$txt['Shop_settings_lucky2_price'] = 'How much the user will win';
$txt['Shop_settings_number_desc'] = 'Here you can define how much the user will win or lose in Numberslots';
$txt['Shop_settings_number_losing'] = 'How much the user will lose';
$txt['Shop_settings_number_complete'] = 'Three numbers payout';
$txt['Shop_settings_number_firsttwo'] = 'First two numbers payout';
$txt['Shop_settings_number_secondtwo'] = 'Last two numbers payout';
$txt['Shop_settings_number_firstlast'] = 'First and last numbers payout';
$txt['Shop_settings_pairs_desc'] = 'Here you can define how much the user will win or lose in Pairs';
$txt['Shop_settings_pairs_losing'] = 'How much the user will lose';
$txt['Shop_settings_pairs_price'] = 'How much the user will win';
$txt['Shop_settings_pairs_clubs'] = 'Clubs';
$txt['Shop_settings_pairs_clubs_1'] = 'Ace of clubs';
$txt['Shop_settings_pairs_clubs_2'] = 'Two of clubs';
$txt['Shop_settings_pairs_clubs_3'] = 'Three of clubs';
$txt['Shop_settings_pairs_clubs_4'] = 'Four of clubs';
$txt['Shop_settings_pairs_clubs_5'] = 'Five of clubs';
$txt['Shop_settings_pairs_clubs_6'] = 'Six of clubs';
$txt['Shop_settings_pairs_clubs_7'] = 'Seven of clubs';
$txt['Shop_settings_pairs_clubs_8'] = 'Eight of clubs';
$txt['Shop_settings_pairs_clubs_9'] = 'Nine of clubs';
$txt['Shop_settings_pairs_clubs_10'] = 'Ten of clubs';
$txt['Shop_settings_pairs_clubs_11'] = 'Jack of clubs';
$txt['Shop_settings_pairs_clubs_12'] = 'Queen of clubs';
$txt['Shop_settings_pairs_clubs_13'] = 'King of clubs';
$txt['Shop_settings_pairs_diamonds'] = 'Diamonds';
$txt['Shop_settings_pairs_diam_1'] = 'Ace of diamonds';
$txt['Shop_settings_pairs_diam_2'] = 'Two of diamonds';
$txt['Shop_settings_pairs_diam_3'] = 'Three of diamonds';
$txt['Shop_settings_pairs_diam_4'] = 'Four of diamonds';
$txt['Shop_settings_pairs_diam_5'] = 'Five of diamonds';
$txt['Shop_settings_pairs_diam_6'] = 'Six of diamonds';
$txt['Shop_settings_pairs_diam_7'] = 'Seven of diamonds';
$txt['Shop_settings_pairs_diam_8'] = 'Eight of diamonds';
$txt['Shop_settings_pairs_diam_9'] = 'Nine of diamonds';
$txt['Shop_settings_pairs_diam_10'] = 'Ten of diamonds';
$txt['Shop_settings_pairs_diam_11'] = 'Jack of diamonds';
$txt['Shop_settings_pairs_diam_12'] = 'Queen of diamonds';
$txt['Shop_settings_pairs_diam_13'] = 'King of diamonds';
$txt['Shop_settings_pairs_hearts'] = 'Hearts';
$txt['Shop_settings_pairs_hearts_1'] = 'Ace of hearts';
$txt['Shop_settings_pairs_hearts_2'] = 'Two of hearts';
$txt['Shop_settings_pairs_hearts_3'] = 'Three of hearts';
$txt['Shop_settings_pairs_hearts_4'] = 'Four of hearts';
$txt['Shop_settings_pairs_hearts_5'] = 'Five of hearts';
$txt['Shop_settings_pairs_hearts_6'] = 'Six of hearts';
$txt['Shop_settings_pairs_hearts_7'] = 'Seven of hearts';
$txt['Shop_settings_pairs_hearts_8'] = 'Eight of hearts';
$txt['Shop_settings_pairs_hearts_9'] = 'Nine of hearts';
$txt['Shop_settings_pairs_hearts_10'] = 'Ten of hearts';
$txt['Shop_settings_pairs_hearts_11'] = 'Jack of hearts';
$txt['Shop_settings_pairs_hearts_12'] = 'Queen of hearts';
$txt['Shop_settings_pairs_hearts_13'] = 'King of hearts';
$txt['Shop_settings_pairs_spades'] = 'Spades';
$txt['Shop_settings_pairs_spades_1'] = 'Ace of spades';
$txt['Shop_settings_pairs_spades_2'] = 'Two of spades';
$txt['Shop_settings_pairs_spades_3'] = 'Three of spades';
$txt['Shop_settings_pairs_spades_4'] = 'Four of spades';
$txt['Shop_settings_pairs_spades_5'] = 'Five of spades';
$txt['Shop_settings_pairs_spades_6'] = 'Six of spades';
$txt['Shop_settings_pairs_spades_7'] = 'Seven of spades';
$txt['Shop_settings_pairs_spades_8'] = 'Eight of spades';
$txt['Shop_settings_pairs_spades_9'] = 'Nine of spades';
$txt['Shop_settings_pairs_spades_10'] = 'Ten of spades';
$txt['Shop_settings_pairs_spades_11'] = 'Jack of spades';
$txt['Shop_settings_pairs_spades_12'] = 'Queen of spades';
$txt['Shop_settings_pairs_spades_13'] = 'King of spades';
$txt['Shop_settings_dice_desc'] = 'Here you can define how much the user will win or lose in Dice';
$txt['Shop_settings_dice_losing'] = 'How much the user will lose';
$txt['Shop_settings_dice_1'] = 'Dice 1 payout';
$txt['Shop_settings_dice_2'] = 'Dice 2 payout';
$txt['Shop_settings_dice_3'] = 'Dice 3 payout';
$txt['Shop_settings_dice_4'] = 'Dice 4 payout';
$txt['Shop_settings_dice_5'] = 'Dice 5 payout';
$txt['Shop_settings_dice_6'] = 'Dice 6 payout';