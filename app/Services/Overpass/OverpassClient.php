<?php

namespace App\Services\Overpass;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OverpassClient
{
    /**
     * Orçamento de tempo do próprio cliente HTTP por tentativa. Fica acima de
     * QUERY_TIMEOUT_SEGUNDOS de propósito, para dar à Overpass API a chance de
     * responder com um erro "limpo" (ex: 504) antes que a nossa conexão morra
     * no meio — sem essa folga, os dois timeouts empatam e a falha vira uma
     * ConnectionException genérica em vez de uma resposta com status.
     */
    private const HTTP_TIMEOUT_SEGUNDOS = 20;

    /**
     * Orçamento de tempo passado para a própria query Overpass QL ([timeout:x]).
     */
    private const QUERY_TIMEOUT_SEGUNDOS = 15;

    /**
     * A API pública da Overpass ocasionalmente responde com erro/timeout sob
     * carga (observado em teste manual: raio grande em área densa gerou 504).
     * Uma segunda tentativa, com uma pequena pausa, resolve a maioria desses
     * casos transitórios sem exigir ação do usuário.
     */
    private const MAX_TENTATIVAS = 2;

    private const INTERVALO_ENTRE_TENTATIVAS_MS = 300;

    /**
     * Tags "sport" do OpenStreetMap tratadas como quadra esportiva. Mantenha
     * em sincronia com App\Services\Overpass\OverpassQuadraFinder::TRADUCAO_ESPORTES.
     * Um "sport"=* sem filtro traz academias, estúdios de pilates e outras
     * instalações que não são quadras.
     */
    private const SPORTS_SUPORTADOS = [
        'soccer',
        'futsal',
        'basketball',
        'tennis',
        'volleyball',
        'beachvolleyball',
        'multi',
    ];

    /**
     * "leisure=sports_centre" sozinho é genérico demais e também casa com
     * academias e estúdios (confirmado contra a API real). Essas continuam
     * de fora mesmo quando têm uma tag "sport" explícita — o que falta cobrir
     * são só as que não têm tag de esporte nenhuma, e isso a Overpass API não
     * tem como distinguir de um clube de verdade sem tag.
     */
    private const SPORTS_EXCLUIDOS_DE_CENTROS = [
        'gymnastics',
        'fitness',
        'pilates',
        'yoga',
    ];

    /**
     * Busca, na Overpass API, elementos do OpenStreetMap com características de
     * quadra/instalação esportiva dentro de um raio a partir de um ponto.
     *
     * @return list<array<string, mixed>>
     */
    public function buscar(float $lat, float $lon, int $raioMetros): array
    {
        for ($tentativa = 1; $tentativa <= self::MAX_TENTATIVAS; $tentativa++) {
            $ultimaTentativa = $tentativa === self::MAX_TENTATIVAS;

            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('services.overpass.user_agent'),
                ])
                    ->timeout(self::HTTP_TIMEOUT_SEGUNDOS)
                    ->asForm()
                    ->post(config('services.overpass.url'), [
                        'data' => $this->montarQuery($lat, $lon, $raioMetros),
                    ]);
            } catch (ConnectionException $e) {
                if ($ultimaTentativa) {
                    throw new OverpassIndisponivelException('Não foi possível conectar à Overpass API.', previous: $e);
                }

                usleep(self::INTERVALO_ENTRE_TENTATIVAS_MS * 1000);

                continue;
            }

            // failed() cobre 4xx e 5xx: uma consulta nossa nunca é malformada
            // (a query é gerada por nós), então qualquer erro aqui — incluindo
            // um 429 de rate limit — indica indisponibilidade, não um pedido ruim.
            if ($response->failed()) {
                if ($ultimaTentativa) {
                    throw new OverpassIndisponivelException("Overpass API retornou status {$response->status()}.");
                }

                usleep(self::INTERVALO_ENTRE_TENTATIVAS_MS * 1000);

                continue;
            }

            return $response->json('elements', []);
        }
    }

    private function montarQuery(float $lat, float $lon, int $raioMetros): string
    {
        $around = "around:{$raioMetros},{$lat},{$lon}";
        $queryTimeout = self::QUERY_TIMEOUT_SEGUNDOS;
        $esportesRegex = implode('|', self::SPORTS_SUPORTADOS);
        $excluidosRegex = implode('|', self::SPORTS_EXCLUIDOS_DE_CENTROS);

        return <<<OVERPASS_QL
        [out:json][timeout:{$queryTimeout}];
        (
          node["leisure"="pitch"]({$around});
          way["leisure"="pitch"]({$around});
          node["leisure"="sports_centre"]["sport"!~"^({$excluidosRegex})$"]({$around});
          way["leisure"="sports_centre"]["sport"!~"^({$excluidosRegex})$"]({$around});
          node["sport"~"^({$esportesRegex})$"]({$around});
          way["sport"~"^({$esportesRegex})$"]({$around});
        );
        out center;
        OVERPASS_QL;
    }
}
