<?php

namespace App\Enums;

enum Esporte: string
{
    case Futebol = 'futebol';
    case Futsal = 'futsal';
    case Volei = 'volei';
    case Basquete = 'basquete';
    case Tenis = 'tenis';
    case BeachTennis = 'beach_tennis';

    public function label(): string
    {
        return match ($this) {
            self::Futebol => 'Futebol',
            self::Futsal => 'Futsal',
            self::Volei => 'Vôlei',
            self::Basquete => 'Basquete',
            self::Tenis => 'Tênis',
            self::BeachTennis => 'Beach Tennis',
        };
    }

    /**
     * Formato de jogo recomendado para o esporte (número de jogadores sugerido).
     *
     * @return array{jogadores: int, descricao: string}
     */
    public function formatoRecomendado(): array
    {
        return match ($this) {
            self::Futebol => ['jogadores' => 22, 'descricao' => '11 x 11'],
            self::Futsal => ['jogadores' => 10, 'descricao' => '5 x 5'],
            self::Volei => ['jogadores' => 12, 'descricao' => '6 x 6'],
            self::Basquete => ['jogadores' => 10, 'descricao' => '5 x 5'],
            self::Tenis => ['jogadores' => 4, 'descricao' => '2 x 2'],
            self::BeachTennis => ['jogadores' => 4, 'descricao' => '2 x 2'],
        };
    }

    /**
     * Formato alternativo (reduzido) de jogo para o esporte.
     *
     * @return array{jogadores: int, descricao: string}
     */
    public function formatoAlternativo(): array
    {
        return match ($this) {
            self::Futebol => ['jogadores' => 12, 'descricao' => '6 x 6'],
            self::Futsal => ['jogadores' => 6, 'descricao' => '3 x 3'],
            self::Volei => ['jogadores' => 8, 'descricao' => '4 x 4'],
            self::Basquete => ['jogadores' => 6, 'descricao' => '3 x 3'],
            self::Tenis => ['jogadores' => 2, 'descricao' => '1 x 1'],
            self::BeachTennis => ['jogadores' => 2, 'descricao' => '1 x 1'],
        };
    }
}
