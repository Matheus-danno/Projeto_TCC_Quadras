<?php

namespace App\Livewire\Perfil;

use App\Models\SuporteMensagem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Ajuda extends Component
{
    /**
     * Perguntas frequentes exibidas no acordeão de ajuda.
     */
    private const PERGUNTAS_FREQUENTES = [
        [
            'pergunta' => 'Como faço uma reserva de quadra?',
            'resposta' => 'Acesse "Quadras", escolha a quadra desejada, selecione a data e o horário disponíveis e confirme o pagamento. A reserva aparece em "Minhas Reservas" assim que for confirmada.',
        ],
        [
            'pergunta' => 'Como cancelo uma reserva?',
            'resposta' => 'Em "Minhas Reservas", clique em cancelar na reserva desejada. Reservas pendentes podem ser canceladas livremente; reservas já confirmadas podem ser canceladas até 5 horas antes do horário marcado, com opção de reembolso em créditos ou estorno.',
        ],
        [
            'pergunta' => 'Como funciona o pagamento das salas?',
            'resposta' => 'Ao entrar em uma sala, cada jogador paga sua parte do valor da quadra (dividido entre os participantes) via Pix ou cartão de crédito. O pagamento é feito no momento em que você confirma sua entrada na sala.',
        ],
        [
            'pergunta' => 'Como funciona o pagamento de uma quadra alugada direto?',
            'resposta' => 'Ao reservar uma quadra diretamente (sem criar uma sala), você paga o valor total da hora via Pix, cartão de crédito ou usando seus créditos disponíveis, na tela de pagamento da reserva.',
        ],
        [
            'pergunta' => 'O que são os créditos da minha conta?',
            'resposta' => 'Créditos são um saldo interno gerado, por exemplo, ao cancelar uma reserva confirmada optando por reembolso em crédito. Eles podem ser usados para pagar reservas futuras diretamente, sem precisar de cartão ou Pix.',
        ],
        [
            'pergunta' => 'Como entro em uma sala criada por outra pessoa?',
            'resposta' => 'Em "Encontre um Time", escolha uma sala com vagas disponíveis e clique para participar. Se a sala exigir aprovação do administrador, seu pedido ficará pendente até ser aceito.',
        ],
        [
            'pergunta' => 'Como falo com o dono da quadra?',
            'resposta' => 'Depois de organizar uma sala em uma quadra, use a aba "Mensagens" no seu perfil para conversar diretamente com o dono sobre dúvidas relacionadas à quadra.',
        ],
    ];

    public string $assunto = '';

    public string $mensagem = '';

    public ?string $mensagemSucesso = null;

    protected function rules(): array
    {
        return [
            'assunto' => ['required', 'string', 'min:3', 'max:120'],
            'mensagem' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'assunto.required' => 'Informe o assunto da sua mensagem.',
            'assunto.min' => 'O assunto deve ter pelo menos 3 caracteres.',
            'mensagem.required' => 'Descreva sua dúvida ou problema.',
            'mensagem.min' => 'Conte um pouco mais para o suporte entender sua dúvida (mínimo 10 caracteres).',
        ];
    }

    public function perguntasFrequentes(): array
    {
        return self::PERGUNTAS_FREQUENTES;
    }

    public function enviarMensagem(): void
    {
        $validated = $this->validate();

        SuporteMensagem::create([
            'user_id' => Auth::id(),
            'assunto' => $validated['assunto'],
            'mensagem' => $validated['mensagem'],
        ]);

        $this->reset('assunto', 'mensagem');
        $this->mensagemSucesso = 'Sua mensagem foi enviada ao suporte. Responderemos no seu e-mail em breve.';
    }

    public function render()
    {
        return view('livewire.perfil.ajuda');
    }
}
