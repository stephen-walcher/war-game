<?php namespace Walcher\CardGame;

interface Deck {
    public function __construct($decks);
    public function shuffle();
    public function deal();
    public function count();
    public function compare();
}