<?php

namespace App\Enums;

enum Esporte: string
{
    case Futebol = 'futebol';
    case Futsal = 'futsal';
    case Volei = 'volei';
    case Basquete = 'basquete';
    case VoleiPraia = 'volei_praia';
    case Tenis = 'tenis';
    case BeachTennis = 'beach_tennis';

    public function label(): string
    {
        return match ($this) {
            self::Futebol => 'Futebol',
            self::Futsal => 'Futsal',
            self::Volei => 'Vôlei',
            self::Basquete => 'Basquete',
            self::VoleiPraia => 'Vôlei de Areia',
            self::Tenis => 'Tênis',
            self::BeachTennis => 'Beach Tennis',
        };
    }

    /**
     * Ícone Bootstrap Icons representativo do esporte.
     */
    public function icone(): string
    {
        return match ($this) {
            self::Futebol, self::Futsal => 'bi-dribbble',
            self::Volei, self::VoleiPraia => 'bi-circle-fill',
            self::Basquete => 'bi-circle-fill',
            self::Tenis, self::BeachTennis => 'bi-record-circle',
        };
    }

    /**
     * Cor de destaque usada nas bordas/ícones dos cards de sala.
     */
    public function cor(): string
    {
        return match ($this) {
            self::Futebol => '#34b6e8',
            self::Futsal => '#34b6e8',
            self::Volei => '#ff7d14',
            self::VoleiPraia => '#ff7d14',
            self::Basquete => '#f77f00',
            self::Tenis => '#01bda5',
            self::BeachTennis => '#97aa00',
        };
    }

    /**
     * Imagem ilustrativa do esporte, usada nos cards de sala.
     */
    public function imagem(): string
    {
        return match ($this) {
            self::Futebol, self::Futsal => 'imagens/tela_inicial/quadra_futebol.png',
            self::Volei => 'imagens/tela_inicial/quadra_volei2.png',
            self::Basquete => 'imagens/tela_inicial/quadra_futebol.png',
            self::Tenis => 'imagens/tela_inicial/quadra_tenis.png',
            self::BeachTennis, self::VoleiPraia => 'imagens/tela_inicial/quadra_volei.png',
        };
    }

    /**
     * Imagem da bola do esporte (extraída do Figma), usada na seleção de esporte
     * ao criar uma sala. Retorna null quando não há imagem correspondente.
     */
    public function imagemBola(): ?string
    {
        return match ($this) {
            self::Futebol => 'imagens/tela_inicial/bola_futebol_society.png',
            self::Futsal => 'imagens/tela_inicial/bola_futsal.png',
            self::Volei => 'imagens/tela_inicial/bola_volei_quadra.png',
            self::VoleiPraia => 'imagens/tela_inicial/bola_volei_areia.png',
            self::Tenis, self::BeachTennis => 'imagens/tela_inicial/bola_beach_tennis.png',
            self::Basquete => null,
        };
    }

    /**
     * Formato de jogo recomendado para o esporte: quantidade de jogadores e descrição.
     *
     * @return array{jogadores: int, descricao: string}
     */
    public function formatoRecomendado(): array
    {
        return match ($this) {
            self::Futebol => ['jogadores' => 22, 'descricao' => '22 jogadores (11 x 11)'],
            self::Futsal => ['jogadores' => 10, 'descricao' => '10 jogadores (5 x 5)'],
            self::Volei => ['jogadores' => 12, 'descricao' => '12 jogadores (6 x 6)'],
            self::Basquete => ['jogadores' => 10, 'descricao' => '10 jogadores (5 x 5)'],
            self::VoleiPraia => ['jogadores' => 4, 'descricao' => '4 jogadores (2 x 2)'],
            self::Tenis => ['jogadores' => 2, 'descricao' => '2 jogadores (1 x 1)'],
            self::BeachTennis => ['jogadores' => 4, 'descricao' => '4 jogadores (2 x 2)'],
        };
    }

    /**
     * Formato alternativo (variação mais informal/reduzida) para o esporte.
     *
     * @return array{jogadores: int, descricao: string}
     */
    public function formatoAlternativo(): array
    {
        return match ($this) {
            self::Futebol => ['jogadores' => 12, 'descricao' => '12 jogadores (6 x 6)'],
            self::Futsal => ['jogadores' => 14, 'descricao' => '14 jogadores (7 x 7)'],
            self::Volei => ['jogadores' => 8, 'descricao' => '8 jogadores (4 x 4)'],
            self::Basquete => ['jogadores' => 6, 'descricao' => '6 jogadores (3 x 3)'],
            self::VoleiPraia => ['jogadores' => 6, 'descricao' => '6 jogadores (3 x 3)'],
            self::Tenis => ['jogadores' => 4, 'descricao' => '4 jogadores (2 x 2, duplas)'],
            self::BeachTennis => ['jogadores' => 2, 'descricao' => '2 jogadores (1 x 1)'],
        };
    }
}
