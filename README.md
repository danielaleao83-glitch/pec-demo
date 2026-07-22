# 🏥 PEC/eSUS APS Laravel DEMO

> Sistema de Prontuário Eletrônico do Cidadão (PEC) desenvolvido em Laravel 12, inspirado na arquitetura do eSUS APS, com foco em boas práticas de desenvolvimento, segurança da informação e conformidade com a LGPD.

---

## 📋 Sobre o Projeto

O **PEC/eSUS APS Laravel DEMO** é um projeto demonstrativo que reproduz a estrutura de um sistema moderno de Prontuário Eletrônico utilizado na Atenção Primária à Saúde.

O objetivo é demonstrar conhecimentos em:

- Arquitetura de software
- Laravel 12
- PHP 8.2+
- PostgreSQL
- Segurança da Informação
- LGPD
- Sistemas de Saúde
- APIs REST
- Integrações Governamentais
- Boas práticas de desenvolvimento

> Este projeto possui finalidade exclusivamente educacional e demonstrativa.

---

# Objetivos

O projeto busca implementar uma base sólida para um sistema de prontuário eletrônico contendo:

- Cadastro de pacientes
- Cadastro de profissionais
- Agenda médica
- Atendimento clínico
- Evolução do paciente
- Prescrição
- Solicitação de exames
- Vacinação
- Histórico clínico
- Painéis administrativos
- Auditoria
- Controle de acesso

---

# Arquitetura

O sistema segue uma arquitetura moderna baseada em:

```
Presentation Layer

↓

Application Layer

↓

Domain Layer

↓

Infrastructure Layer

↓

Database
```

Princípios utilizados:

- DDD (Domain Driven Design)
- SOLID
- Repository Pattern
- Service Layer
- Clean Code
- REST API
- MVC Laravel

---

# Stack Tecnológica

| Tecnologia | Versão |
|------------|---------|
| Laravel | 12 |
| PHP | 8.2+ |
| PostgreSQL | 16 |
| Redis | Opcional |
| Bootstrap | 5 |
| JavaScript | ES2023 |
| Composer | 2 |
| NodeJS | 22 |
| Vite | Atual |

---

# Estrutura do Projeto

```
app/

bootstrap/

config/

database/

docs/

public/

resources/

routes/

storage/

tests/
```

---

# Módulos

## Pacientes

- Cadastro
- Histórico
- Documentos
- Contatos

---

## Profissionais

- Cadastro
- Especialidades
- CNS
- Conselho profissional

---

## Agenda

- Consultas
- Retornos
- Agendamentos

---

## Atendimento

- SOAP
- CID-10
- CIAP-2
- Evolução
- Encaminhamento

---

## Exames

- Solicitação
- Resultado
- Histórico

---

## Vacinação

- Registro
- Histórico
- Campanhas

---

## Administração

- Usuários
- Perfis
- Permissões
- Auditoria

---

# Segurança

O projeto considera diversas práticas de segurança:

- Autenticação
- Autorização
- Controle por Perfis
- Logs
- Auditoria
- Criptografia
- Senhas Hash
- CSRF
- XSS Protection
- SQL Injection Protection

---

# LGPD

O projeto considera requisitos da Lei Geral de Proteção de Dados.

Exemplos:

- Consentimento
- Controle de acesso
- Registro de auditoria
- Minimização de dados
- Criptografia
- Backup
- Rastreabilidade

---

# Integrações Futuras

- eSUS APS
- CADSUS
- CNES
- CNS
- SIGTAP
- CID-10
- CIAP-2
- TISS
- FHIR
- HL7

---

# Roadmap

## Fase 1

- Estrutura Laravel

- Banco de dados

- Autenticação

- Usuários

---

## Fase 2

- Cadastro de pacientes

- Agenda

- Atendimento

---

## Fase 3

- Prescrição

- Exames

- Vacinação

---

## Fase 4

- APIs

- Dashboard

- Auditoria

---

## Fase 5

- Integrações SUS

- FHIR

- HL7

---

# Documentação

A documentação técnica encontra-se em:

```
docs/
```

---

# Tecnologias

- Laravel
- PHP
- PostgreSQL
- Composer
- Bootstrap
- JavaScript
- HTML5
- CSS3

---

# Licença

Projeto de demonstração para fins acadêmicos e portfólio.

Não possui vínculo oficial com o Ministério da Saúde.

---

# Desenvolvido por

**Daniela Leão da Silva**

Projeto demonstrativo para estudo de arquitetura de sistemas de saúde utilizando Laravel.
