# Mapa de páginas — Presenza (contexto do Assistente)

> **Produto: Presenza** — *presenças reais, certificados à prova.*
> Gerado em 21/10/2026 a partir de `routes/web.php` + telas em `resources/views/`. **Fonte de conhecimento do assistente de IA** (RF30, Q43) junto com os demais docs desta pasta.
> **Manutenção:** toda vez que uma tela ou rota mudar, atualize a linha correspondente — este documento é o "mapa do prédio" que a IA consulta. Revisão completa em cada alteração de portal.

Legenda das telas: código-fonte em `resources/views/**`. Requisitos referenciados: `docs/requisitos-p1.md`.

## 1. Páginas públicas (sem login) — onde o assistente aparece

| Rota | Tela | Conteúdo em uma frase |
|---|---|---|
| `GET /` | `welcome.blade.php` | Página inicial — marca Presenza, slogan, descrição do produto (presença por reconhecimento facial, fila de espera, tickets, certificado com verificação pública por QR) e 2 botões: **Verificar certificado** e **Acessar portais**. |
| `GET /verificar` | `verificar/busca.blade.php` | Formulário de **verificação pública de certificado**: campo de código (ex.: `PZ-2025-000101`), texto "insira o código impresso no certificado ou escaneie o QR". |
| `GET /verificar/{codigo}` | `verificar/resultado.blade.php` | Resultado da verificação: título grande **VÁLIDO / EXPIRADO / NÃO ENCONTRADO** + dados mínimos (nome, treinamento, carga horária, conclusão, válido até, instrutores), QR quando válido e botão **Baixar PDF** (RF21/RF22). |
| `GET /verificar/{codigo}/pdf` | `verificar/pdf.blade.php` | Download do **PDF** do certificado (mesma visão usada pela emissão, `pdf/documento.blade.php`). |
| *(login e recuperação de senha)* | `auth/*.blade.php` | Login/senha (Breeze) e fluxo de reset por e-mail (RF01). O cadastro autônomo (`/register`) está **desativado** — contas só via RH/Líder (RF05). |

## 2. Autenticado — entrada e termo LGPD

| Rota | Tela | Conteúdo |
|---|---|---|
| `GET/POST /portal/termo` | `portal/termo.blade.php` | **Aceite do termo LGPD** de biometria (RF04) — obrigatório antes de qualquer portal; nova versão do termo re-pede aceite. |

## 3. Portal do colaborador / líder / instrutor (`layouts/portal.blade.php`)

| Rota | Tela | Conteúdo |
|---|---|---|
| `GET /portal` | `portal/home.blade.php` | Home por papel: minhas aulas/treinamentos, minhas vagas/reservas, notificações (sino), botão abrir ticket (RF10), status do termo. Líder vê gestão do seu setor; instrutor vê suas aulas. |
| `POST /portal/tickets` | — | Colaborador **abre ticket** (tipo do catálogo + texto livre; RF10/RF11). |
| `POST /portal/presenca/facial` | — | Colaborador valida a **própria presença** pelo reconhecimento facial, só com aula ativa (RF03/RF14.a). |
| `GET /portal/aulas/{aula}` | `portal/aula.blade.php` | Tela de **chamada do instrutor**: iniciar/encerrar aula, validar presença facial dos alunos e **homologação manual** (senha do colaborador + motivo do catálogo ou novo; RF14.b/RF15/RF16). |
| `POST /portal/aulas/{aula}/iniciar|encerrar` | — | Controle da janela da aula pelo instrutor — notifica o RH (RF06). |
| `GET/POST /profile` | `profile/edit.blade.php` | Perfil (Breeze) + alterar senha + excluir conta. |

## 4. Portal RH / Admin (`/portal/rh/*`, guardado por escopo — RF05)

| Rota | Conteúdo (RF) |
|---|---|
| `/portal/rh/reservas` | Reservas nominais por sessão + cancelamento que **promove a fila** (RF08/RF09). |
| `/portal/rh/tickets` | Resolver tickets: cancelar vaga ou criar justificativa (RF12). |
| `/portal/rh/justificativas` | Registrar/alterar status APROVADO/RECUSADO a qualquer momento (RF12/RF13). |
| `/portal/rh/certificados` | Revisar elegibilidade (presença em todas as aulas, RF18), **emitir** e **reemitir** (imutável, nova versão; RF19/RF20). |
| `/portal/rh/treinamentos` | CRUD de treinamentos e aulas (Treinamento 1:N Aula; líder só no próprio setor; RF06/RF07). |
| `/portal/rh/usuarios` | CRUD de usuários, ativar/desativar, resetar senha (RF05). |
| `/portal/rh/relatorios` | Relatórios + exportação CSV (por sessão, pessoa; RF28) e **matriz de conformidade** em `/portal/rh/conformidade`. |
| `/portal/rh/configuracoes` | Configurações: motivos de homologação, tipos de ticket, versões do termo LGPD, SMTP (RF26; SMTP só Admin). |

## 5. O que o assistente de IA deve fazer com este mapa

- Explicar **onde cada coisa acontece** ("para verificar um certificado, use a página de verificação — o link é `/verificar`"), **como funciona** o fluxo (vaga nominal, fila, aula, chamada, homologação, certificado) e **quais papéis** podem fazer o quê (seção 1 de `requisitos-p1.md`).
- **Nunca** citar nem pedir dado pessoal (RF32); **nunca** conferir código de certificado no chat — indicar `/verificar` (Q56); **nunca** prometer ações (o assistente não faz nada, só explica — Q37).
- Fora do tema Presenza → recusa educada (RF31).
