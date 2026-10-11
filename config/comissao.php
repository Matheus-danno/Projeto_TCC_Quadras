<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Percentual padrão de comissão
    |--------------------------------------------------------------------------
    |
    | Usado apenas como valor inicial quando ainda não existe nenhuma linha
    | em "comissao_configuracoes" (bootstrap do sistema). Depois disso, a
    | porcentagem vigente é sempre a da configuração mais recente, alterável
    | pelo administrador na tela de Configurações.
    |
    */

    'percentual_padrao' => (float) env('COMISSAO_PERCENTUAL_PADRAO', 5.0),

];
