<?php

namespace App\Livewire\Perfil;

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Fortify;
use Livewire\Component;

class Seguranca extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $mensagemSenha = null;

    public bool $twoFactorEnabled = false;

    public bool $mostrarConfiguracao2fa = false;

    public string $qrCodeSvg = '';

    public string $manualSetupKey = '';

    public string $codigo = '';

    public array $codigosRecuperacao = [];

    public bool $mostrarCodigosRecuperacao = false;

    public ?string $mensagem2fa = null;

    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $user = Auth::user();

        if (Fortify::confirmsTwoFactorAuthentication() && ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication($user);
        }

        $this->twoFactorEnabled = $user->fresh()->hasEnabledTwoFactorAuthentication();
    }

    protected function rules(): array
    {
        return [
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ];
    }

    public function atualizarSenha(): void
    {
        try {
            $validated = $this->validate();
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update(['password' => $validated['password']]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->mensagemSenha = 'Senha atualizada com sucesso.';
    }

    public function iniciarConfiguracao2fa(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(Auth::user());

        $user = Auth::user()->fresh();

        $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
        $this->manualSetupKey = decrypt($user->two_factor_secret);
        $this->mostrarConfiguracao2fa = true;
        $this->mensagem2fa = null;
    }

    public function confirmar2fa(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate(['codigo' => ['required', 'string', 'size:6']]);

        $confirmTwoFactorAuthentication(Auth::user(), $this->codigo);

        $this->twoFactorEnabled = true;
        $this->mostrarConfiguracao2fa = false;
        $this->reset('codigo', 'qrCodeSvg', 'manualSetupKey');
        $this->mensagem2fa = 'Autenticação de dois fatores ativada com sucesso.';
    }

    public function cancelarConfiguracao2fa(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(Auth::user());

        $this->reset('codigo', 'qrCodeSvg', 'manualSetupKey', 'mostrarConfiguracao2fa');
        $this->resetErrorBag();
    }

    public function desativar2fa(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(Auth::user());

        $this->twoFactorEnabled = false;
        $this->mostrarCodigosRecuperacao = false;
        $this->codigosRecuperacao = [];
        $this->mensagem2fa = 'Autenticação de dois fatores desativada.';
    }

    public function mostrarRecuperacao(): void
    {
        $this->codigosRecuperacao = Auth::user()->fresh()->recoveryCodes();
        $this->mostrarCodigosRecuperacao = true;
    }

    public function gerarNovosCodigos(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(Auth::user());

        $this->mostrarRecuperacao();
        $this->mensagem2fa = 'Novos códigos de recuperação gerados.';
    }

    public function render()
    {
        return view('livewire.perfil.seguranca');
    }
}
