# 🏥 PEC/eSUS APS Laravel DEMO

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![LGPD](https://img.shields.io/badge/LGPD-Conforme-198754?style=for-the-badge&logo=shield&logoColor=white)
![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-0D6EFD?style=for-the-badge)
![License](https://img.shields.io/badge/License-Demo-6C757D?style=for-the-badge)

---

# 🏥 Sistema de Prontuário Eletrônico do Cidadão (PEC)

Sistema desenvolvido em **Laravel 12**, inspirado na arquitetura do **eSUS APS**, com foco em arquitetura moderna, APIs REST, segurança da informação e conformidade com a LGPD.

O projeto simula uma plataforma de **Prontuário Eletrônico do Cidadão**, seguindo conceitos utilizados em sistemas de saúde digital:

- Cadastro e gerenciamento de pacientes
- Organização de atendimentos
- Agenda de profissionais
- Controle de informações clínicas
- Segurança e rastreabilidade de dados
- Arquitetura preparada para integrações futuras

---

# 📸 Screenshots

## Visão Geral do Sistema

![Sistema PEC/eSUS APS](assets/images/sistema.png)

---

# 📋 Sobre o Projeto

O **PEC/eSUS APS Laravel DEMO** é um sistema demonstrativo desenvolvido para representar a estrutura de um **Prontuário Eletrônico do Cidadão (PEC)** aplicado ao cenário da Atenção Primária à Saúde.

A aplicação foi construída utilizando boas práticas do ecossistema Laravel, buscando uma arquitetura organizada, segura e preparada para crescimento.

O objetivo principal é demonstrar conhecimentos em desenvolvimento de sistemas complexos, especialmente soluções voltadas para o setor de saúde.

---

# 🎯 Objetivos do Projeto

- Criar uma base moderna para um sistema PEC
- Aplicar arquitetura MVC utilizando Laravel
- Desenvolver módulos organizados e escaláveis
- Implementar boas práticas de segurança
- Demonstrar integração entre backend, banco de dados e frontend
- Criar uma solução preparada para futuras integrações

---

# 🚀 Tecnologias Utilizadas

## Backend

- Laravel 12
- PHP 8.2+
- Composer 2
- Laravel Sanctum
- Laravel Reverb
- Laravel Pulse

## Banco de Dados

- PostgreSQL 16

## Frontend

- Blade Templates
- Bootstrap 5
- JavaScript
- Vite

## Cache e Processamento

- Redis
- Filas Laravel
- Jobs
- Events

---

# 🏗️ Arquitetura da Aplicação

Estrutura baseada no padrão MVC do Laravel:

```
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


database/
├── migrations/
├── factories/
└── seeders/


resources/
├── views/
├── css/
└── js/


routes/
├── web.php
└── api.php
```

---

# 🩺 Funcionalidades

## 👤 Cadastro de Pacientes

- Registro de pacientes
- Dados pessoais
- Informações cadastrais
- Histórico básico

---

## 📅 Agenda Médica

- Organização de consultas
- Controle de horários
- Gestão de profissionais
- Preparação para calendário clínico

---

## 📊 Dashboard Administrativo

- Indicadores gerais
- Visão operacional
- Resumo do sistema
- Monitoramento das informações

---

## 🔐 Segurança

Implementações e planejamentos:

- Controle de acesso
- Proteção de dados
- Auditoria de ações
- Organização de permissões
- Boas práticas LGPD

---

# 🔒 LGPD e Proteção de Dados

O projeto considera princípios importantes para sistemas de saúde:

- Proteção de dados pessoais
- Controle de acesso aos dados
- Registro de atividades
- Segurança das informações sensíveis
- Separação de responsabilidades

---

# 🔌 Integrações Futuras

Planejamento de evolução:

## 🏥 Saúde Digital

- HL7 FHIR
- Integração com sistemas externos
- APIs para comunicação de dados clínicos

## 🔏 Segurança

- Assinatura digital ICP-Brasil
- Certificados digitais
- Validação de documentos

## 📄 Documentos

- Geração de PDF
- Relatórios médicos
- QR Code de identificação

---

# ⚙️ Instalação

## Requisitos

Antes de iniciar:

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

## Configurar banco de dados

Edite:

```
.env
```

Configure:

```
DB_CONNECTION=pgsql
DB_DATABASE=pec_esus
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

Executar migrations:

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

Executar testes automatizados:

```bash
php artisan test
```

---

# 📁 Estrutura de Imagens

O README utiliza:

```
assets/
└── images/
    └── sistema.png
```

---

# 📌 Status Atual

🚧 **Projeto em desenvolvimento**

Próximas evoluções:

- Mais módulos clínicos
- API completa
- Integração FHIR
- Controle avançado de permissões
- Melhorias de interface
- Auditoria completa

---

# 👩‍💻 Desenvolvimento

Projeto criado para:

- Estudo avançado de Laravel
- Demonstração profissional
- Portfólio de desenvolvimento
- Pesquisa em sistemas de saúde digital

---

# 📄 Licença

Este projeto possui finalidade:

✅ Educacional  
✅ Demonstração técnica  
✅ Portfólio profissional  

Não representa o sistema oficial **e-SUS APS** do Ministério da Saúde.

---

⭐ Se este projeto foi útil ou interessante, considere deixar uma estrela no repositório.
