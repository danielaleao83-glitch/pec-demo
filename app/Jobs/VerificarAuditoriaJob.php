<?php

namespace App\Jobs;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

use App\Services\Auditoria\AuditoriaIntegrityChecker;
use App\Jobs\VerificarAuditoriaJob;

class VerificarAuditoriaJob extends Command
{
    /**
     * 🧾 Assinatura do comando
     */
    protected $signature = 'auditoria:monitor
                            {--sync : Executa de forma síncrona}
                            {--force : Ignora lock de execução}';

    /**
     * 📄 Descrição
     */
    protected $description = 'Monitor de integridade da auditoria (hash + cadeia)';

    /**
     * 🚀 Execução principal
     */
    public function handle(): int
    {
        $this->info('🔍 Iniciando verificação de auditoria...');

        try {

            // 🔒 Evita execução concorrente
            if (!$this->option('force') && !$this->acquireLock()) {
                $this->warn('⚠ Já existe uma verificação em andamento.');
                return self::SUCCESS;
            }

            /**
             * 🔥 MODO SYNC (DEBUG REAL)
             */
            if ($this->option('sync')) {

                // ⚠️ aqui é onde geralmente quebra
                $checker = app(AuditoriaIntegrityChecker::class);

                if (!$checker) {
                    throw new \Exception('AuditoriaIntegrityChecker não resolvido no container');
                }

                $resultado = $checker->verificar();

                $this->info('✔ Auditoria executada (sync)');

                $this->newLine();
                $this->line('📊 RESULTADO:');
                $this->line(json_encode($resultado, JSON_PRETTY_PRINT));
                $this->newLine();

                if (!isset($resultado['valido'])) {
                    throw new \Exception('Formato inválido do retorno da auditoria');
                }

                if (!$resultado['valido']) {
                    $this->error('🚨 AUDITORIA COMPROMETIDA!');
                } else {
                    $this->info('✅ Auditoria íntegra');
                }

            } else {

                /**
                 * 🚀 MODO FILA
                 */
                dispatch(new VerificarAuditoriaJob());

                $this->info('📦 Job enviado para fila');
            }

            return self::SUCCESS;

        } catch (Throwable $e) {

            /**
             * 🚨 ERRO REAL (AGORA NÃO ESCONDE MAIS)
             */
            $this->newLine();
            $this->error('❌ ERRO REAL NA AUDITORIA:');
            $this->error($e->getMessage());

            $this->newLine();
            $this->warn('📍 ARQUIVO: ' . $e->getFile());
            $this->warn('📍 LINHA: ' . $e->getLine());

            $this->newLine();
            $this->warn('📍 TRACE COMPLETO:');
            $this->line($e->getTraceAsString());

            Log::critical('ERRO NO MONITOR DE AUDITORIA', [
                'erro' => $e->getMessage(),
                'linha' => $e->getLine(),
                'arquivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;

        } finally {

            // ⚠️ evita erro se acquireLock falhou antes
            try {
                $this->releaseLock();
            } catch (\Throwable $e) {
                // ignora erro de lock
            }
        }
    }

    /**
     * 🔒 Lock simples (anti concorrência)
     */
    private function acquireLock(): bool
    {
        return cache()->add(
            'auditoria_monitor_lock',
            true,
            now()->addMinutes(5)
        );
    }

    /**
     * 🔓 Libera lock
     */
    private function releaseLock(): void
    {
        cache()->forget('auditoria_monitor_lock');
    }
}