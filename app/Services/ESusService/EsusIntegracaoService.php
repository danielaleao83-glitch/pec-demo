<?php

namespace App\Services\Integracoes;

use App\Models\Atendimento;
use App\Models\ConfiguracaoMunicipal;
use App\Services\AuditoriaService;
use Illuminate\Support\Facades\Log;

class EsusIntegracaoService
{
    /**
     * Gera XML do atendimento individual e-SUS
     */
    public function gerarXmlAtendimento(int $atendimentoId): ?string
    {
        try {
            $atendimento = Atendimento::with([
                'paciente',
                'profissional',
                'estabelecimento',
                'procedimentos',
            ])->findOrFail($atendimentoId);

            $config = ConfiguracaoMunicipal::firstOrFail();

            $xml = new \SimpleXMLElement('<atendimentoIndividual/>');

            // Informações do município e configuração
            $xml->addChild('municipio', $config->municipio);
            $xml->addChild('uf', $config->uf);
            $xml->addChild('cnes', $config->cnes_padrao);
            $xml->addChild('versaoLayout', $config->versao_layout);

            // Dados do atendimento
            $xml->addChild('dataAtendimento', $atendimento->data_atendimento);

            // Dados sensíveis: criptografia ou mascaramento
            $xml->addChild('cpfProfissional', $this->mascararCpf($atendimento->profissional->cpf));
            $xml->addChild('cnsPaciente', $this->mascararCns($atendimento->paciente->cns));

            // Procedimentos realizados
            $procedimentosXml = $xml->addChild('procedimentos');
            foreach ($atendimento->procedimentos as $proc) {
                $p = $procedimentosXml->addChild('procedimento');
                $p->addChild('codigo', $proc->codigo);
                $p->addChild('descricao', htmlspecialchars($proc->descricao));
            }

            // Auditoria de geração do XML
            AuditoriaService::registrar(
                'gerar_xml_atendimento',
                'esus',
                $atendimento->id,
                null,
                ['usuario_id' => auth()->id() ?? null]
            );

            return $xml->asXML();

        } catch (\Throwable $e) {
            Log::error('Erro ao gerar XML e-SUS', [
                'atendimento_id' => $atendimentoId,
                'erro' => $e->getMessage(),
            ]);

            AuditoriaService::registrar(
                'erro_gerar_xml',
                'esus',
                $atendimentoId,
                null,
                ['erro' => $e->getMessage()]
            );

            return null;
        }
    }

    /**
     * Mascarar CPF para envio seguro
     */
    private function mascararCpf(string $cpf): string
    {
        return substr($cpf, 0, 3).'.***.***-'.substr($cpf, -2);
    }

    /**
     * Mascarar CNS para envio seguro
     */
    private function mascararCns(string $cns): string
    {
        return substr($cns, 0, 4).'**********'.substr($cns, -4);
    }
}
