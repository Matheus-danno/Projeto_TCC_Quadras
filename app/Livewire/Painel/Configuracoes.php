<?php

namespace App\Livewire\Painel;

use App\Concerns\ProfileValidationRules;
use Flux\Concerns\InteractsWithComponents;
use Livewire\Component;

class Configuracoes extends Component
{
    use InteractsWithComponents, ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $nomeEstabelecimento = '';

    public string $telefone = '';

    public string $endereco = '';

    public string $cidade = '';

    public string $estado = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->nomeEstabelecimento = $user->nome_estabelecimento ?? '';
        $this->telefone = $user->telefone ?? '';
        $this->endereco = $user->endereco ?? '';
        $this->cidade = $user->cidade ?? '';
        $this->estado = $user->estado ?? '';
    }

    protected function rules(): array
    {
        return array_merge($this->profileRules(auth()->id()), [
            'nomeEstabelecimento' => ['required', 'string', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'endereco' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
        ]);
    }

    public function cnpjFormatado(): string
    {
        $cnpj = auth()->user()->cnpj;

        if (! $cnpj || strlen($cnpj) !== 14) {
            return $cnpj ?? '';
        }

        return substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'.'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
    }

    public function salvar(): void
    {
        $validated = $this->validate();

        $user = auth()->user();

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nome_estabelecimento' => $validated['nomeEstabelecimento'],
            'telefone' => preg_replace('/\D/', '', $validated['telefone']),
            'endereco' => $validated['endereco'],
            'cidade' => $validated['cidade'],
            'estado' => strtoupper($validated['estado']),
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->toast('Dados atualizados com sucesso.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.painel.configuracoes');
    }
}
