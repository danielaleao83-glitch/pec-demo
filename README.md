# 🏥 PEC/eSUS APS Laravel DEMO

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![LGPD](https://img.shields.io/badge/LGPD-Conforme-198754?style=for-the-badge)
![Status](https://img.shields.io/badge/Status-Em%20Desenvolvimento-0D6EFD?style=for-the-badge)

------------------------------------------------------------------------

## 🏥 Sistema de Prontuário Eletrônico do Cidadão (PEC)

Sistema demonstrativo desenvolvido em **Laravel 12**, inspirado na
arquitetura do **eSUS APS**, para fins de estudo, demonstração técnica e
portfólio.

> **Aviso:** Este projeto **não possui vínculo oficial com o Ministério
> da Saúde** e **não representa um sistema homologado**.

## 📸 Screenshot

![Sistema](assets/images/sistema.png)

------------------------------------------------------------------------

## 📋 Sobre

O projeto demonstra uma arquitetura moderna para um sistema de
Prontuário Eletrônico do Cidadão (PEC), utilizando Laravel, PostgreSQL e
boas práticas de desenvolvimento.

### Objetivos

- Arquitetura MVC
- APIs REST
- Segurança e LGPD
- Organização em camadas
- Preparação para integrações HL7 FHIR

## 🚀 Stack

- Laravel 12
- PHP 8.2+
- PostgreSQL 16
- Bootstrap 5
- Vite
- Redis (planejado)

## 📁 Estrutura

``` text
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

## ⚙️ Instalação

``` bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
npm run dev
```

## 🧪 Testes

``` bash
php artisan test
```

## 📌 Status

🚧 Em desenvolvimento.

## 👩‍💻 Desenvolvido por

Daniela Leão da Silva

## 📄 Licença

Projeto para fins educacionais, técnicos e de portfólio.
