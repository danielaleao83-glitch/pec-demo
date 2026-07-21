<?php

namespace App\Jobs;

use App\Models\FilaNotificacao;
use App\Services\SmsService;
use App\Services\VozService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnviarNotificacaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notif;

    public $tries = 3;

    public function __construct(FilaNotificacao $notif)
    {
        $this->notif = $notif;
    }

    public function handle()
    {
        try {

            // 1. WhatsApp
            app(WhatsAppService::class)->enviar($this->notif);

            $this->notif->update([
                'status' => 'enviado',
                'canal' => 'whatsapp',
            ]);

        } catch (\Exception $e) {

            try {
                // 2. SMS fallback
                app(SmsService::class)->enviar($this->notif);

                $this->notif->update([
                    'status' => 'enviado',
                    'canal' => 'sms',
                ]);

                // 3. Crítico → ligação
                if ($this->notif->prioridade === 'critica') {
                    app(VozService::class)->ligar($this->notif);
                }

            } catch (\Exception $e2) {

                $this->notif->increment('tentativas');

                $this->notif->update([
                    'status' => 'falhou',
                    'ultimo_erro' => $e2->getMessage(),
                ]);
            }
        }
    }
}
