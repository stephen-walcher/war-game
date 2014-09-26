<?php
require_once('vendor/autoload.php');

// Define parameters
$user_count = 5;
$deck_count = 1;

// Set up and create users
$users = [];

for ($i = 0; $i < $user_count; $i++) {
    array_push($users, new User);
}

// Create deck(s)
$deck = new Deck($deck_count);
$deck->shuffle();

// Deal out cards to the users
$cards_left = true;

do {
    list($key, $user) = each($users);
    
    if (is_null($user)) {
        if ($deck->count() < count($users)) {
            // Not enough cards to go around again. Stop here
            $cards_left = false;
            break;
        }

        reset($users);
        
        list($key, $user) = each($users);
    }
    
    try {
        $user->receive(
            $deck->deal()
        );

    } catch (DeckEmptyException $e) {
        $cards_left = false;
    }
    
    unset($key, $user);

} while ($cards_left);

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
        foreach ($users as $uid => $u) {
            if ($at_war) {
                if ($u->cardCount() < 2) {
                    print "User {$uid} does not have enough cards to continue this war!!" . PHP_EOL;
                        
                    // Remove from future processing
                    unset($users[$uid]);
                        
                    // No need to stay in the loop
                    break;
    
                } else {
                    // We are at war, son. Deal a face down card to set aside
                    try {
                        $set_aside[] = $u->play();
    
                    } catch (OutOfCardsException $e) {
                        // This user's out of cards!
                        print "User {$uid} is out of cards!!\r\n";
                        
                        // Remove from future processing
                        unset($users[$uid]);
                        
                        // No need to stay in the loop
                        break;
                    }
                }
            }
            
            if ($u->cardCount() < 1) {
                print "User {$uid} does not have enough cards to continue!!" . PHP_EOL;
                    
                // Remove from future processing
                unset($users[$uid]);

            } else {
                try {
                    $in_play[$uid] = $u->play();
    
                } catch (OutOfCardsException $e) {
                    // This user's out of cards!
                    print "User {$uid} is out of cards!!" . PHP_EOL;
                        
                    // Remove from future processing
                    unset($users[$uid]);
                }
            }
        }

        print 'Cards in play: ' . count($in_play) . "\r\n";

        if (count($in_play) <= 1) {
            // Only one user is left to play cards. We have a winner!
            // Give them their card back and shut it down
            print 'Not enough users played! We have a winner!' . PHP_EOL;

            // If there's a card left, give to the winner to round out.
            if (count($in_play) == 1) {
                $winner_uid = key($in_play);
                
                $winner = $users[$winner_uid];
                $winner->receive(current($in_play));
                
                print "Winner is Player {$winner_uid} with a total of " . $winner->cardCount() . ' cards!' . PHP_EOL;
            }

            $keep_playing = false;
            $at_war = false;
            
            break(2);
        }
    
        print "Players:\t" . implode("\t", array_keys($users)) . PHP_EOL;
        print "-------------------------------------------------------------------------------------------------------" . PHP_EOL;
        print "Cards:\t\t" . implode("\t", $in_play) . PHP_EOL;
    
        // Determine the victor or if there is a war
        $result = $deck->compare($in_play);

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

    $winner = $users[$winner_uid];
    
    foreach ($set_aside as $card) {
        $winner->receive($card);
    }

    print "Winner: Player {$winner_uid}" . PHP_EOL;
    print "Player {$winner_uid} received: " . implode(',', $set_aside) . "\r\n";

    // Status update
    print "Standings:\r\n";
    print "Players:\t" . implode("\t", array_keys($users)) . "\r\n";
    
    print "-------------------------------------------------------------------------------------------------------\r\n";
    
    print "Cards:\t\t";
    foreach ($users as $uid => $u) {
        print "{$u->cardCount()}\t";
    }
    
    print PHP_EOL . PHP_EOL;
    
    if ($hand_count > 3500) {
        print 'These players are pretty evenly matched! They have played 3500 hands, so we will call it a tie to save memory' . PHP_EOL . PHP_EOL;
        
        $keep_playing = false;
    }

} while ($keep_playing);