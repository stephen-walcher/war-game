<?php namespace Walcher\CardGame;

interface Game {
    public function __construct($users, $deck);
    public function deal();
    public function play();
}