<?php namespace Walcher\WarGame;

use Walcher\CardGame;

class Game implements \Walcher\CardGame\Game {
    private $users,
            $deck;

    /**
     * Validates user and deck objects and instantiates holder variables
     *
     * @param array $users Array of User objects
     * @param Deck  $deck  Deck object
     *
     * @throws UserArrayNotFound
     * @throws DeckNotFound
     */
    public function __construct($users = false, $deck = false)
    {
        // User(s) and deck required
        if ($users == false && !is_array($users) && count($users) > 0) {
            throw new UserArrayNotFound;
            return;
        }

        if ($deck == false && !($deck instanceof Walcher\WarGame\Deck)) {
            throw new DeckNotFound;
            return;
        }

        // Instantiate our holder variables with the desired type.
        // Function can be overwritten
        $this->users = $users;
        $this->deck = $deck;
    }

    /**
     * Shuffle the deck and deal cards to all users
     * Checks number of remaining cards before new round and stops dealing
     * if there aren't enough cards to go around
     */
    public function deal() {
        $this->deck->shuffle();

        // Deal out cards to the users
        $cards_left = true;

        do {
            list($key, $user) = each($this->users);

            if (is_null($user)) {
                if ($this->deck->count() < count($this->users)) {
                    // Not enough cards to go around again. Stop here
                    $cards_left = false;
                    break;
                }

                reset($this->users);

                list($key, $user) = each($this->users);
            }

            try {
                $user->receive(
                    $this->deck->deal()
                );

            } catch (DeckEmptyException $e) {
                $cards_left = false;
            }

            unset($key, $user);

        } while ($cards_left);

        print PHP_EOL;

        print 'Starting game' . PHP_EOL;

        print "Players:\t" . implode("\t", array_keys($this->users)) . PHP_EOL;

        print "-------------------------------------------------------------------------------------------------------" . PHP_EOL;

        print "Cards:\t\t";
        foreach ($this->users as $uid => $u) {
            print "{$u->cardCount()}\t";
        }

        print PHP_EOL . PHP_EOL;
    }

    /**
     * Play the game!
     * Loop through all users and ask them to play cards. Evaluate
     * results and, if needed, evaluate wars that arise. Calculate
     * card standings and determine winner.
     */
    public function play() {
        // Start the game!
        $hand_count = 0; // Current count of hands for limiter.
        $keep_playing = true; // Variable to change if all user runs out of cards
        $at_war = false; // Variable to change to keep adding cards for wars

        do {
            print PHP_EOL;
            print 'Hand #' . ++$hand_count . ':' . PHP_EOL;

            // Define card holder
            $set_aside = [];

            do {
                // Define result holder
                $result = false;

                // Define card holder
                $in_play = []; 

                // Each user plays a card
                foreach ($this->users as $uid => $u) {
                    if ($at_war) {
                        if ($u->cardCount() < 2) {
                            print "User {$uid} does not have enough cards to continue this war!!" . PHP_EOL;

                            // Make sure all remaining cards from the user get added back to the pot so none are lost
                            if ($this->users[$uid]->cardCount() > 0) {
                                $user_has_cards = true;

                                do {
                                    try {
                                        $set_aside[] = $this->users[$uid]->play();
                                        print "User {$uid} donating leftover card to pot" . PHP_EOL;

                                    } catch (OutOfCardsException $e) {
                                        // User is out of cards, step out of loop
                                        $user_has_cards = false;

                                        print "User {$uid} has donated all remaining cards" . PHP_EOL;
                                    }

                                } while ($user_has_cards);
                            }

                            // Remove from future processing
                            unset($this->users[$uid]);

                            // No need to stay in the loop
                            continue;

                        } else {
                            // We are at war, son. Deal a face down card to set aside
                            try {
                                $set_aside[] = $u->play();

                            } catch (OutOfCardsException $e) {
                                // This user's out of cards!
                                print "User {$uid} is out of cards!!\r\n";

                                // Make sure all remaining cards from the user get added back to the pot so none are lost
                                if ($this->users[$uid]->cardCount() > 0) {
                                    $user_has_cards = true;

                                    do {
                                        try {
                                            $set_aside[] = $this->users[$uid]->play();
                                            print "User {$uid} donating leftover card to pot" . PHP_EOL;

                                        } catch (OutOfCardsException $e) {
                                            // User is out of cards, step out of loop
                                            $user_has_cards = false;

                                            print "User {$uid} has donated all remaining cards" . PHP_EOL;
                                        }

                                    } while ($user_has_cards);
                                }

                                // Remove from future processing
                                unset($this->users[$uid]);

                                // No need to stay in the loop
                                continue;
                            }
                        }
                    }

                    if ($u->cardCount() < 1) {
                        print "User {$uid} does not have enough cards to continue!!" . PHP_EOL;

                        // Remove from future processing
                        unset($this->users[$uid]);

                    } else {
                        try {
                            $in_play[$uid] = $u->play();

                        } catch (OutOfCardsException $e) {
                            // This user's out of cards!
                            print "User {$uid} is out of cards!!" . PHP_EOL;

                            // Remove from future processing
                            unset($this->users[$uid]);
                        }
                    }
                }

                print 'Cards in play: ' . count($in_play) . "\r\n";

                if (count($in_play) <= 1) {
                    // Only one user is left to play cards. We have a winner!
                    // Give them their card back and shut it down
                    print 'Not enough users played! Game Over!' . PHP_EOL;

                    // If there's a card left, give to the winner to round out.
                    if (count($in_play) == 1) {
                        $winner_uid = key($in_play);

                        $winner = $this->users[$winner_uid];
                        $winner->receive(current($in_play));

                        // Give the winner the remaining set aside cards
                        foreach ($set_aside as $card) {
                            $winner->receive($card);
                        }

                        print "Winner is Player {$winner_uid} with a total of " . $winner->cardCount() . ' cards!' . PHP_EOL;

                    } else {
                        print 'There was no winner this game. No player could continue the round.' . PHP_EOL;
                    }

                    $keep_playing = false;
                    $at_war = false;

                    break(2);
                }

                print "Players:\t" . implode("\t", array_keys($this->users)) . PHP_EOL;
                print "-------------------------------------------------------------------------------------------------------" . PHP_EOL;
                print "Cards:\t\t" . implode("\t", $in_play) . PHP_EOL;

                // Determine the victor or if there is a war
                $result = $this->deck->compare($in_play);

                // Set aside cards in play
                foreach ($in_play as $card) {
                    array_push($set_aside, $card);
                }

                if ($result == 'WAR') {
                    // War protocols are in effect!
                    print 'We are at war!!' . PHP_EOL;

                    $at_war = true;

                } else {
                    $at_war = false;
                }

            } while ($at_war);

            // Award the hand's card to the winner
            $winner_uid = key($result);

            $winner = $this->users[$winner_uid];

            foreach ($set_aside as $card) {
                $winner->receive($card);
            }

            print "Winner: Player {$winner_uid}" . PHP_EOL;
            print "Player {$winner_uid} received: " . implode(',', $set_aside) . "\r\n";

            // Status update
            print "Standings:\r\n";
            print "Players:\t" . implode("\t", array_keys($this->users)) . "\r\n";

            print "-------------------------------------------------------------------------------------------------------\r\n";

            print "Cards:\t\t";
            foreach ($this->users as $uid => $u) {
                print "{$u->cardCount()}\t";
            }

            print PHP_EOL . PHP_EOL;

            if ($hand_count > 3500) {
                print 'These players are pretty evenly matched! They have played 3500 hands, so we will call it a tie to save memory' . PHP_EOL . PHP_EOL;

                $keep_playing = false;
            }

        } while ($keep_playing);
    }
}

class UserArrayNotFound extends \Exception {
    protected $message = 'Array of Users needs to be provided to play the War Game';
}

class DeckNotFound extends \Exception {
    protected $message = 'A valid Deck needs to be provided to play the War Game';
}