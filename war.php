<?php
/**
 * Sample War Game
 *
 * A CLI-based war game simulator for PHP.
 *
 * @package Walcher
 * @subpackage WarGame
 * @author Stephen Walcher <stephenwalcher@gmail.com>
 */

require_once('vendor/autoload.php');

use Walcher\CardGame;
use Walcher\WarGame;

// Define parameters based on arguments provided or defaults
$options = getopt('', ["users::","decks::"]);

if (isset($options['users']) && preg_match('/^[0-9]+$/', $options['users']) > 0) {
    $user_count = $options['users'];

} else {
    $user_count = 2;
}

if (isset($options['decks']) && preg_match('/^[0-9]+$/', $options['decks']) > 0) {
    $deck_count = $options['decks'];

} else {
    $deck_count = 1;
}

// Create and set up users
$users = [];

for ($i = 0; $i < $user_count; $i++) {
    array_push($users, new Walcher\WarGame\User);
}

// Create deck(s)
$deck = new Walcher\WarGame\Deck($deck_count);

// Create the War Game
try {
    $game =
        new Walcher\WarGame\Game(
            $users,
            $deck
        );

} catch (Exception $e) {
    die($e->getMessage());
}

// Shuffle and deal the cards
$game->deal();

// Start the game
$game->play();