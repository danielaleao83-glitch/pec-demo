# 🏥 PEC/eSUS APS Laravel DEMO

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?logo=postgresql&logoColor=white)
![LGPD](https://img.shields.io/badge/LGPD-Compliant-success)
![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-blue)

---

# Sistema de Prontuário Eletrônico do Cidadão (PEC)

Sistema desenvolvido em **Laravel 12**, inspirado na arquitetura do **eSUS APS**, com foco em arquitetura moderna, boas práticas de desenvolvimento, APIs REST, segurança da informação e conformidade com a **Lei Geral de Proteção de Dados (LGPD)**.

> **Projeto demonstrativo para fins acadêmicos, técnicos e de portfólio.**
>
> Este projeto **não possui vínculo oficial com o Ministério da Saúde** e **não representa um sistema homologado**.

---

# 📋 Sobre o Projeto

O objetivo deste projeto é demonstrar competências em desenvolvimento de sistemas para saúde utilizando tecnologias modernas.

## Principais objetivos

- Arquitetura de Software
- Laravel 12
- PHP 8.2+
- PostgreSQL
- APIs REST
- Segurança da Informação
- LGPD
- Sistemas de Saúde
- Boas práticas de desenvolvimento
- Integrações futuras com o ecossistema do SUS

---

# 🏗 Arquitetura

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

### Princípios adotados

- Domain Driven Design (DDD)
- SOLID
- Clean Architecture
- Repository Pattern
- Service Layer
- MVC Laravel
- REST API
- Clean Code

---

# 🚀 Stack Tecnológica

| Tecnologia | Versão |
|------------|---------|
| Laravel | 12 |
| PHP | 8.2+ |
| PostgreSQL | 16 |
| Redis | Opcional |
| Bootstrap | 5 |
| JavaScript | ES2023 |
| Composer | 2 |
| Node.js | 22 |
| Vite | Atual |

---

# 📁 Estrutura do Projeto

```text
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

# 📦 Módulos

| Módulo | Status |
|---------|--------|
| Cadastro de Pacientes | 🚧 |
| Cadastro de Profissionais | 🚧 |
| Agenda Médica | 🚧 |
| Atendimento Clínico | 🚧 |
| SOAP | 🚧 |
| CID-10 | 🚧 |
| CIAP-2 | 🚧 |
| Prescrição | Planejado |
| Solicitação de Exames | Planejado |
| Vacinação | Planejado |
| Administração | 🚧 |
| Auditoria | Planejado |

---

# 🔒 Segurança

O sistema considera diversas práticas de segurança:

- Autenticação
- Autorização
- Controle por Perfis
- Auditoria
- Logs
- Hash de Senhas
- Criptografia
- Proteção CSRF
- Proteção XSS
- Proteção contra SQL Injection

---

# 🛡 LGPD

O projeto foi concebido considerando princípios da Lei Geral de Proteção de Dados.

## Controles previstos

- Consentimento
- Controle de acesso
- Auditoria
- Minimização de dados
- Criptografia
- Backup
- Rastreabilidade

---

# 🔗 Integrações Futuras

| Sistema | Situação |
|----------|----------|
| eSUS APS | Planejada |
| CADSUS | Planejada |
| CNES | Planejada |
| CNS | Planejada |
| SIGTAP | Planejada |
| CID-10 | Planejada |
| CIAP-2 | Planejada |
| TISS | Planejada |
| HL7 | Planejada |
| FHIR | Planejada |

---

# 🗺 Roadmap

## Fase 1

- ✅ Estrutura Laravel
- ✅ Banco de Dados
- ✅ Autenticação
- 🚧 Usuários

## Fase 2

- Cadastro de Pacientes
- Agenda
- Atendimento

## Fase 3

- Prescrição
- Exames
- Vacinação

## Fase 4

- APIs REST
- Dashboard
- Auditoria

## Fase 5

- Integrações SUS
- HL7
- FHIR

---

# 📚 Documentação

A documentação técnica encontra-se na pasta:

```text
docs/
├── arquitetura.md
├── banco-de-dados.md
├── seguranca-lgpd.md
├── integracoes-sus.md
└── roadmap.md
```

---

# 💻 Tecnologias Utilizadas

- Laravel
- PHP
- PostgreSQL
- Composer
- Bootstrap
- JavaScript
- HTML5
- CSS3

---

# 📄 Licença

Projeto desenvolvido para fins **educacionais, técnicos e de portfólio**.

Não possui vínculo oficial com o Ministério da Saúde.

---

# 👩‍💻 Desenvolvido por

**Daniela Leão da Silva**

Projeto demonstrativo de arquitetura de sistemas para Atenção Primária à Saúde utilizando Laravel 12.
