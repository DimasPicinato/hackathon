<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>

<processo-presenza>
# Processo do Hackathon — Presenza (obrigatório em toda sessão de IA)

Este repositório é o projeto do Hackathon FATEC & ETEC · Usina Lins, desenvolvido **com IA, processo 100% registrado** para a banca. Duas obrigações permanentes:

1. **Diário de bordo** — ao fim de qualquer interação com resultado relevante (decisão, artefato criado/alterado, conclusão, prompt importante), registre uma sessão numerada no `journey-log.md` seguindo a skill `registro-hackathon` (`.agents/skills/registro-hackathon/SKILL.md`), que também define o **polimento de prompts** do time (contexto → objetivo → restrições → formato, preservando o sentido).

2. **Relatório de entrega** — `docs/relatorio-final.md` é o documento-base do **PDF final exigido pela banca** (ATO 1 síntese + ATO 2 detalhado com todos os prompts). **Toda sessão registrada no journey-log deve também atualizar o relatório**, sem esperar pedido do time:
   - prompt polido novo → **§6** com numeração sequencial (P-40, P-41, …) + resultado;
   - resumo da sessão → **§9** (diário de bordo do relatório);
   - cronologia → **§8** quando aplicável;
   - números/estado (testes, RFs, credenciais, URL) → **ATO 1** e **§10**.
   Regras completas na própria skill (`Manutenção do relatório final de entrega`).

Regras de trabalho do time: AT01–AT04 em `docs/acordos-de-trabalho.md` (gate de aprovação para qualquer dúvida/alternativa, Docker conforme AT02, instalações sempre instruídas, processo registrado).
</processo-presenza>
