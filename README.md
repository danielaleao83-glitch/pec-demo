# 🏥 PEC/eSUS APS Laravel DEMO

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![LGPD](https://img.shields.io/badge/LGPD-Conforme-198754?style=for-the-badge&logo=shield&logoColor=white)
![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-0D6EFD?style=for-the-badge)
![License](https://img.shields.io/badge/License-Demo-6C757D?style=for-the-badge)

---

# 🏥 Sistema de Prontuário Eletrônico do Cidadão (PEC)

Sistema desenvolvido em **Laravel 12**, inspirado na arquitetura do **eSUS APS**, com foco em:

- Arquitetura moderna
- APIs REST
- Segurança da informação
- Organização de dados clínicos
- Boas práticas de desenvolvimento
- Conformidade com a LGPD

> ⚠️ Projeto demonstrativo para fins acadêmicos, técnicos e de portfólio.  
> Não possui vínculo oficial com o Ministério da Saúde e não representa um sistema homologado.

---

# 📸 Screenshots

## Visão Geral do Sistema

![Sistema PEC/eSUS APS](assets/images/sistema.png)

---

# 📋 Sobre o Projeto

O **PEC/eSUS APS Laravel DEMO** é um sistema demonstrativo que representa a estrutura de um **Prontuário Eletrônico do Cidadão (PEC)** aplicado ao cenário da **Atenção Primária à Saúde**.

O objetivo é demonstrar a construção de uma aplicação moderna para o setor de saúde utilizando o ecossistema Laravel, seguindo conceitos de arquitetura escalável, segurança e organização de código.

O projeto contempla conceitos como:

- Cadastro e gerenciamento de pacientes
- Organização de atendimentos
- Gestão de agenda
- Estrutura para informações clínicas
- Controle de acesso
- Auditoria de operações
- Preparação para integrações futuras

---

# 🎯 Objetivos do Projeto

- Desenvolver uma base moderna para um sistema PEC
- Aplicar arquitetura MVC utilizando Laravel
- Criar uma estrutura escalável e organizada
- Demonstrar desenvolvimento de sistemas complexos
- Implementar boas práticas de segurança
- Preparar uma base para integrações com sistemas de saúde

---

# 🏗️ Arquitetura da Aplicação

O projeto segue uma arquitetura organizada baseada em:

- MVC Laravel
- Service Layer
- Repository Pattern
- SOLID
- Clean Code
- REST API
- Separação de responsabilidades

Arquitetura conceitual:

```
Presentation Layer
        │
Application Layer
        │
Domain Layer
        │
Infrastructure Layer
        │
Database
```

---

# 🚀 Stack Tecnológica

## Backend

| Tecnologia | Versão |
|---|---|
| Laravel | 12 |
| PHP | 8.2+ |
| Composer | 2 |
| Laravel Sanctum | API Authentication |
| Laravel Reverb | Comunicação em tempo real |
| Laravel Pulse | Monitoramento |

---

## Banco de Dados

| Tecnologia | Versão |
|---|---|
| PostgreSQL | 16 |

---

## Frontend

| Tecnologia | Versão |
|---|---|
| Blade | Laravel |
| Bootstrap | 5 |
| JavaScript | ES2023 |
| Vite | Atual |

---

## Infraestrutura

- Redis
- Cache Laravel
- Filas
- Jobs
- Events
- Queues

---

# 📁 Estrutura do Projeto

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
│
├── Models/
│
├── Services/
│
└── Policies/


bootstrap/

config/

database/
├── migrations/
├── factories/
└── seeders/


docs/

public/

resources/
├── views/
├── css/
└── js/


routes/
├── web.php
└── api.php


storage/

tests/
```

---

# 🩺 Funcionalidades e Módulos

| Módulo | Status |
|---|---|
| Cadastro de Pacientes | 🚧 Em desenvolvimento |
| Cadastro de Profissionais | 🚧 Em desenvolvimento |
| Agenda Médica | 🚧 Em desenvolvimento |
| Atendimento Clínico | 🚧 Em desenvolvimento |
| Dashboard Administrativo | 🚧 Em desenvolvimento |
| SOAP | 📌 Planejado |
| CID-10 | 📌 Planejado |
| CIAP-2 | 📌 Planejado |
| Prescrição | 📌 Planejado |
| Exames | 📌 Planejado |
| Vacinação | 📌 Planejado |
| Auditoria | 📌 Planejado |

---

# 👤 Cadastro de Pacientes

Funcionalidades previstas:

- Cadastro de pacientes
- Dados pessoais
- Informações cadastrais
- Histórico clínico
- Organização dos registros

---

# 📅 Agenda Médica

Funcionalidades previstas:

- Organização de consultas
- Controle de horários
- Gestão de profissionais
- Planejamento de calendário clínico

---

# 📊 Dashboard Administrativo

Recursos:

- Indicadores gerais
- Visão operacional
- Resumo do sistema
- Monitoramento de informações

---

# 🔒 Segurança e LGPD

O projeto considera práticas importantes para sistemas de saúde:

- Autenticação segura
- Controle de permissões
- Proteção CSRF
- Proteção contra XSS
- Proteção contra SQL Injection
- Hash de senhas
- Auditoria
- Logs
- Criptografia de informações sensíveis
- Controle de acesso aos dados

---

# 🔌 Integrações Futuras

Planejamento de integração com:

| Sistema | Status |
|---|---|
| eSUS APS | Planejado |
| CADSUS | Planejado |
| CNES | Planejado |
| CNS | Planejado |
| SIGTAP | Planejado |
| HL7 | Planejado |
| FHIR | Planejado |
| TISS | Planejado |

---

# 🗺️ Roadmap

## Fase 1 - Base do Sistema

✅ Estrutura Laravel  
✅ Banco de Dados  
✅ Autenticação  

🚧 Usuários e permissões

---

## Fase 2 - Módulos Principais

🚧 Cadastro de Pacientes

🚧 Agenda

🚧 Atendimento

---

## Fase 3 - Recursos Clínicos

📌 Prescrição

📌 Exames

📌 Vacinação

---

## Fase 4 - Integrações

📌 APIs REST

📌 HL7/FHIR

📌 Integrações SUS

---

# ⚙️ Instalação

## Requisitos

- PHP 8.2+
- Composer 2+
- PostgreSQL 16
- Node.js 22+
- NPM

---

## Clonar o projeto

```bash
git clone https://github.com/seuusuario/pec-esus-laravel.git

cd pec-esus-laravel
```

---

## Instalar dependências

Backend:

```bash
composer install
```

Frontend:

```bash
npm install
```

---

## Configurar ambiente

Criar arquivo `.env`:

```bash
cp .env.example .env
```

Gerar chave:

```bash
php artisan key:generate
```

---

## Banco de Dados

Configurar no arquivo:

```
.env
```

Exemplo:

```env
DB_CONNECTION=pgsql
DB_DATABASE=pec_esus
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Executar:

```bash
php artisan migrate --seed
```

---

# ▶️ Executando o Projeto

Servidor Laravel:

```bash
php artisan serve
```

Frontend:

```bash
npm run dev
```

Acesse:

```
http://localhost:8000
```

---

# 🧪 Testes

Executar testes:

```bash
php artisan test
```

---

# 📚 Documentação

Documentação técnica:

```text
docs/

├── arquitetura.md
├── banco-de-dados.md
├── seguranca-lgpd.md
├── integracoes-sus.md
└── roadmap.md
```

---

# 📁 Imagens do README

Estrutura:

```text
assets/
└── images/
    └── sistema.png
```

---

# 📌 Status Atual

🚧 **Projeto em desenvolvimento**

Próximas evoluções:

- Novos módulos clínicos
- API completa
- Integração FHIR
- Auditoria avançada
- Controle de permissões
- Melhorias de interface

---

# 👩‍💻 Desenvolvido por

**Daniela Leão da Silva**

Projeto desenvolvido para:

- Portfólio profissional
- Estudos avançados em Laravel
- Demonstração de arquitetura de software
- Pesquisa em sistemas de saúde digital

---

# 📄 Licença

Este projeto possui finalidade:

✅ Educacional  
✅ Demonstração técnica  
✅ Portfólio profissional  

Não representa o sistema oficial **e-SUS APS** do Ministério da Saúde.

---

⭐ Se este projeto chamou sua atenção, considere deixar uma estrela no repositório.
