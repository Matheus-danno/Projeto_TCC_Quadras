<?php

namespace App\Livewire\Quadras;

use App\Services\Overpass\OverpassIndisponivelException;
use App\Services\Overpass\OverpassQuadraFinder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Proximas extends Component
{
    private const RAIOS_PERMITIDOS_KM = [1, 3, 5, 10, 20];

    public ?float $latitude = null;

    public ?float $longitude = null;

    public int $raioKm = 5;

    public bool $permissaoNegada = false;

    public ?string $erro = null;

    protected OverpassQuadraFinder $finder;

    public function boot(OverpassQuadraFinder $finder): void
    {
        $this->finder = $finder;
    }

    public function buscarPorLocalizacao(float $lat, float $lng): void
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->permissaoNegada = false;
        $this->erro = null;
    }

    public function marcarPermissaoNegada(): void
    {
        $this->permissaoNegada = true;
        $this->erro = null;
    }

    public function atualizarRaio(int $km): void
    {
        abort_unless(in_array($km, self::RAIOS_PERMITIDOS_KM, true), 422);

        $this->raioKm = $km;
    }

    /**
     * @return list<int>
     */
    public function raiosDisponiveis(): array
    {
        return self::RAIOS_PERMITIDOS_KM;
    }

    #[Computed]
    public function quadras(): Collection
    {
        if ($this->latitude === null || $this->longitude === null) {
            return collect();
        }

        try {
            return $this->finder->buscar($this->latitude, $this->longitude, $this->raioKm * 1000);
        } catch (OverpassIndisponivelException) {
            $this->erro = 'Não foi possível buscar quadras próximas agora. Tente novamente em alguns instantes.';

            return collect();
        }
    }

    public function render()
    {
        // Resolve o computed antes do Blade renderizar: $erro é um efeito colateral
        // de quadras(), e a view precisa do valor já definido para o alerta de erro.
        return view('livewire.quadras.proximas', [
            'quadras' => $this->quadras,
        ]);
    }
}
