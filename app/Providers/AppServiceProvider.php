<?php

namespace App\Providers;

use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Quadra;
use App\Models\Reserva;
use App\Policies\PedidoPolicy;
use App\Policies\ProdutoPolicy;
use App\Policies\QuadraPolicy;
use App\Policies\ReservaPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::policy(Quadra::class, QuadraPolicy::class);
        Gate::policy(Reserva::class, ReservaPolicy::class);
        Gate::policy(Produto::class, ProdutoPolicy::class);
        Gate::policy(Pedido::class, PedidoPolicy::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
