# Prompts originais do time — extração do histórico de conversas

> **Anexo de evidência do PDF de prompts (`docs/prompts-ia.pdf`).**
>
> Este documento reúne, **na íntegra (verbatim)**, os prompts que o time enviou ao agente de IA durante o hackathon, extraídos do histórico real das conversas (banco de sessões do agente OpenCode, SQLite).
>
> - Cada bloco traz o **texto original** (sem edição), o **horário** e o **resultado** obtido.
> - Os prompts **polidos** (contexto → objetivo → restrições → formato), com numeração `P-01…P-41`, estão no **ATO 2 §6** de `docs/relatorio-final.md`; aqui está a **fonte bruta**, para a banca conferir a completude do processo.
> - Extração reproduzível: `php scripts/extrair-prompts-conversas.php <opencode.db> docs/prompts-conversas.md` (ver cabeçalho do script).

## Sessões recuperadas

| Sessão | Rótulo | Quando | Natureza |
|---|---|---|---|
| `ses_eaab1bdb` | Controle de Skills / autenticação (outro repositório) | 19–20/10 | Outro repositório (fora do pacote do hackathon) — **2 prompts** |
| `ses_ea6477f7` | Curadoria de skills do agente | 20/10 | Outro repositório (fora do pacote do hackathon) — **5 prompts** |
| `ses_ea594ad6` | Principal do hackathon (Presenza) | 20–21/10 | Projeto do hackathon — **40 prompts** |
| `ses_ea2bce65` | Skill global para buscar e corrigir bugs | 21/10 | Projeto do hackathon — **2 prompts** |
| `ses_ea1300d8` | Planejamento de assistente de IA no Presenza | 21/10 | Projeto do hackathon — **2 prompts** |
| `ses_ea12c729` | Correção do PDF de prompts e extração das conversas | 21/10 | Projeto do hackathon — **2 prompts** |

**Total recuperado: 53 prompts enviados pelo time**, distribuídos em 6 sessões do agente.

---

## Sessão Controle de Skills / autenticação (outro repositório)

- **Janela:** 19–20/10
- **Diretório do agente:** `C:/Projects/dp-hub`
- **Escopo:** Outro repositório (fora do pacote do hackathon)

### C-01 · 19/10 14:55

> **Prompt do time (texto original):**
>
> identifique o que ainda precisa ser feito em relação à autenticação, mas não faça nenhuma alteração

**Resultado:** Inventário do que faltava em autenticação levantado **sem alterar nada** — sessão de outro repositório, mantida fora do pacote do hackathon.

### C-02 · 20/10 12:38

> **Prompt do time (texto original):**
>
> blz, complete todas essas fases

**Resultado:** Plano de execução das fases fechado em modo de planejamento (sem implementação) — também de outro repositório, fora do escopo deste hackathon.


## Sessão Curadoria de skills do agente

- **Janela:** 20/10
- **Diretório do agente:** `C:/Projects/dp-hub`
- **Escopo:** Outro repositório (fora do pacote do hackathon)

### C-03 · 20/10 11:31

> **Prompt do time (texto original):**
>
> observe as skills que você possui, remova desse projeto as skills que julga ser redundantes e procure skills que possam auxiliar o projeto

**Resultado:** Mapeamento das skills do projeto feito, proposta de remoção das redundantes e busca de skills de apoio (arquitetura, qualidade, clones) (diário, sessão 5).

### C-04 · 20/10 11:51

> **Prompt do time (texto original):**
>
> ... (continuação do que eu estava escrevendo e cortei de resposta) ... por trazer itens relevantes de qualidade para o desenvolvimento, pode meter bala agora

**Resultado:** Remoção da skill `design` redundante, instalação das 4 skills aprovadas no escopo do projeto e busca das skills populares de arquitetura/qualidade (diário, sessão 5).

### C-05 · 20/10 12:25

> **Prompt do time (texto original):**
>
> inclusive, quero todas essas skills que está instalando em nível global e não só desse projeto

**Resultado:** Skills instaladas também no **escopo global** do usuário (`~/.agents/skills/`), incluindo a Firecrawl que estava pendente (diário, sessão 5).

### C-06 · 20/10 12:28

> **Prompt do time (texto original):**
>
> quando eu disse que quero todas essas skills instaladas no global, eu quis dizer as que você está adicionando agora e aquelas que não estão relacionadas ao contexto ou especializada na arquitetura desse projeto de agora

**Resultado:** Regra corrigida e aplicada: skill genérica (não acoplada ao contexto/arquitetura do repo) fica **também** no global; a `shadcn` permaneceu no projeto por ser adaptada ao repositório (diário, sessão 5).

### C-07 · 20/10 12:34

> **Prompt do time (texto original):**
>
> quero que a cópia local do projeto das skills globais sejam removidas do local, mantendo apenas uma cópia no global

**Resultado:** 7 cópias idênticas removidas do projeto (mantida apenas a global), com verificação de referências quebradas (diário, sessões 5 e 12).


## Sessão Principal do hackathon (Presenza)

- **Janela:** 20–21/10
- **Diretório do agente:** `C:/Users/dimas/OneDrive/Fatec/hackaton`
- **Escopo:** Projeto do hackathon

### C-08 · 20/10 14:46

> **Prompt do time (texto original):**
>
> quero que me explique e estruture uma arquitetura para cada projeto, sem envolver tecnologias ainda, mas avaliando o que precisaremos fazer de escopo para cada um dos problemas, todos estão presentes no @problemas.docx

**Resultado:** Arquitetura conceitual dos 3 problemas **sem tecnologias**, com escopo de cada um, extraída do `problemas.docx`; base comum (identidade, elegibilidade, biometria com via alternativa, auditoria, LGPD, fachada única de hardware) e comparativo de escopo/risco (diário, sessão 2).

### C-09 · 20/10 14:55

> **Prompt do time (texto original):**
>
> faça uma pequena skill nesse próprio repositório aqui, que a cada operação/trabalho/documentação/discussão com IA, que nós fizermos, você deve adicionar num .md resumido explicando o que fizemos, em ordem e quando, assim servindo de documentação do que iremos apresentar no hackathon amanhâ

**Resultado:** Skill `registro-hackathon` + arquivo de registro criados, com 3 entradas iniciais e o campo **“Prompts-chave”** já pensando no PDF da banca (diário, sessão 3).

### C-10 · 20/10 15:07

> **Prompt do time (texto original):**
>
> bem, para todo caso, os meios de reconhecimento do usuário, utilizaremos sempre como opção o reconhecimento facial, por ser o meio que conseguimos mais fácil e a equipe já lidou com isso antes, o método alternativo, será por login e senha normal como fallback do reconhecimento facial caso esteja indisponível por qualquer motivo, login e senha será comum e vinculado ao usuário, assim como o reconhecimento facial também, inclusive, quero saber a estrutura que precisaremos usar para armazenar os dados necessários para reconhecimento facial, apesar da tecnologia não ser definida agora, o banco de dados usado será o PostgreSQL por sua versatilidade para todas as questões, dito isso, prossiga com o planejamento resumido novamente. também seguiremos com o versionamento desse repositório com git e GitHub já sincronizados. inclua também que praticamente todo prompt que enviarmos, você deve polir ele pra ficar mais legível e deve ser documentado os resultados, altere isso na skill

**Resultado:** Skill ampliada (polimento de prompts + registro de resultados), Git inicializado com commit inicial, estrutura de armazenamento biométrico mapeada e **PostgreSQL** fixado; planejamento resumido reemitido (diário, sessões 4 e 5).

### C-11 · 20/10 15:12

> **Prompt do time (texto original):**
>
> altere o nome do arquivo da documentação para algo que remeta a diário de bordo acho que inglês fica mais daora, também remova a regra que nada pode ser alterado, assim, deve-se evitar, mas caso for útil para apresentar melhor o histórico do projeto, então prefira alterar sim

**Resultado:** `registro-sessoes.md` → **`journey-log.md`** (via `git mv`, histórico preservado) e política de edição das entradas flexibilizada na skill e no cabeçalho (diário, sessão 6).

### C-12 · 20/10 15:28

> **Prompt do time (texto original):**
>
> agora vamos conversar brevemente em poucas palavras, qual projeto a gente deve escolher, e por quê?

**Resultado:** Recomendação objetiva: **P1** (viabilidade em 24h, “wow” verificável pelo celular, risco LGPD controlado); P3 como plano B e P2 como o mais arriscado (diário, sessão 7).

### C-13 · 20/10 15:31

> **Prompt do time (texto original):**
>
> inclusive, desde que iniciei o repositório, já dava pra fazer push pra origin.

**Resultado:** `origin` já configurada (`github.com/DimasPicinato/hackaton`) e push confirmado pela IA; sessão registrada e commitada (diário, sessões 7–8).

### C-14 · 20/10 15:32

> **Prompt do time (texto original):**
>
> blz, escolheremos a 1

**Resultado:** **P1 travado.** A arquitetura detalhada foi aberta e depois revertida a pedido do time (preservada no histórico do Git, commit `c081de1`) — o passo seguinte virou levantamento de requisitos (diário, sessão 8).

### C-15 · 20/10 15:39

> **Prompt do time (texto original):**
>
> primeiro, sem definir arquitetura ou nada ainda, remova isso do journey, procure alguma skill de levantamento de requisitos pra IA fazer comigo um questionário e depois faça isso comigo

**Resultado:** Documento de arquitetura removido do pacote de trabalho (preservado no Git) e busca da skill de levantamento de requisitos feita — `grill-me`/`grilling` (mattpocock/skills) selecionadas (diário, sessão 9).

### C-16 · 20/10 15:43

> **Prompt do time (texto original):**
>
> como eu posso resolver o seu problema com o powershell? passa pra mim agora rapidinho que eu resolvo

**Resultado:** Causa do bloqueio identificada (ExecutionPolicy do PowerShell barrando `npx.ps1`) e comando de liberação entregue ao time — **instalação sempre instruída** (AT03) (diário, sessão 9).

### C-17 · 20/10 15:44

> **Prompt do time (texto original):**
>
> ae, autorizei, pode prosseguir

**Resultado:** `npx` liberado e `grill-me` instalado (≈1,4 mi de instalações); `grilling` escolhida para o questionário (diário, sessão 10).

### C-18 · 20/10 15:47

> **Prompt do time (texto original):**
>
> faça o questionário pelo OpenCode

**Resultado:** Questionário interativo iniciado no próprio OpenCode, sempre com a opção recomendada em primeiro lugar (diário, sessão 10).

### C-19 · 20/10 15:52

> **Prompt do time (texto original):**
>
> não entendi as perguntas, deixe-as mais claras e exemplifique melhor

**Resultado:** Perguntas reescritas com situação concreta de exemplo em cada uma; rodada refeita (diário, sessão 10).

### C-20 · 20/10 20:23

> **Prompt do time (texto original):**
>
> continue

**Resultado:** Rodada 3 do questionário refeita (o envio anterior falhou por formato) — **Q1–Q30** fechadas (diário, sessão 10).

### C-21 · 20/10 21:12

> **Prompt do time (texto original):**
>
> o que precisamos ajustar é, pra confirmar presença, tanto o colaborador, quanto o instrutor podem fazer o reconhecimento facial para marcar presença, seja o colaborador de forma autônoma durante a aula, seja o instrutor também durante a aula, inclusive o instrutor também pode cadastrar motivo na hora de preencher o motivo para o caso de validar presença manualmente, também será exigido a senha de acesso do colaborador caso precisar cadastrar manualmente o motivo da presença.

**Resultado:** —

### C-22 · 20/10 21:25

> **Prompt do time (texto original):**
>
> então você acredita que consegue seguir muito bem com o projeto a partir dos requisitos que foram levantados e documentados?

**Resultado:** Confiança alta nos requisitos (ciclo completo e rastreável RF/RNF), 3 pendências externas mapeadas (SMTP, fotos para o seed, ensaio do simulador) e sequência de execução proposta (diário, sessão 11).

### C-23 · 20/10 21:34

> **Prompt do time (texto original):**
>
> crie um .md para armazenar todo o conteúdo desse questionário (perguntas e respostas) e o referencie onde for relevante

**Resultado:** Transcrição Q&A salva em `docs/questionario-requisitos-p1.md` e referenciada no cabeçalho dos requisitos e no diário (diário, sessão 10).

### C-24 · 20/10 22:04

> **Prompt do time (texto original):**
>
> vamos decidir uma paleta de cores e um conjunto de skills para design e UI/UX

**Resultado:** Paleta e tipografia decididas com o dataset da skill `ui-ux-pro-max` (**verde agro + laranja de segurança**, Lexend + Source Sans 3) e kit de skills de design fechado → `docs/design-p1.md` (diário, sessão 13).

### C-25 · 20/10 22:18

> **Prompt do time (texto original):**
>
> qual nome damos ao projeto?

**Resultado:** 5 candidatas com racional (Certia, TurmaCert, NRFlow, Assidu, **Presenza**) → o time escolheu **Presenza**, com a tagline “presenças reais, certificados à prova” (diário, sessão 14).

### C-26 · 20/10 22:26

> **Prompt do time (texto original):**
>
> ainda falta algo à definir? há alguma pendência? algo pra documentar? se não, só confirmar que não e aguardar minhas instruções pra já começar a planejar o sistema

**Resultado:** Confirmação de que não havia pendências de definição; as 3 pendências externas (SMTP do time, fotos dos integrantes, ensaio do simulador) ficaram documentadas como não bloqueantes (diário, sessões 11 e 13).

### C-27 · 20/10 22:51

> **Prompt do time (texto original):**
>
> bem vamos aos próximos passos então, vamos decidir a stack, apesar de eu usar e ser muito mais proficiente com Node, TS, NestJS e React, para um MVP acho de extrema importancia, até pela velocidade e ai-friendly algo mais como um Ruby-on-Rails, Laravel, Django, o que for mais amigável pra esse contexto considerando que esses frameworks são full-stacks e cobrem bem as necessidades do projeto, principalmente considerando que você irá desenvolver tudo sem eu botar a mão no código.
> definido as vantagens e motivos para usar a tecnologia em questão, a gente prossegue pro desenvolvimento. Considere que:
> Usaremos Docker para orquestrar os serviços que o projeto usar, tanto no desenvolvimento, quanto em homologação, serão usados os mesmos serviços, recomendo ter um docker-compose para os serviços, que será usado durante o desenvolvimento e testes, e um outro que extende os serviços desse docker-compose com a adição da aplicação buildada e as variáveis de ambiente dela hard-code no docker-compose para compatibilidade de rede, o .env normal durante o desenvolvimento pode usar o localhost para acessar os serviços que subir com o docker-compose de desenvolvimento.
> (Documente essa próxima instru
> 
> *(trecho condensado pelo entregável: o prompt original colou o stack trace completo — 64 frames — do `ParseError`; o cabeçalho do erro, a requisição `GET /portal/aulas/3`, a rota `ChamadaController@aula` e o arquivo `resources/views/portal/aula.blade.php:24` estão íntegros acima)*

**Resultado:** Comparativo de stacks pelos critérios dos RFs + regras fixas **AT01/AT02/AT03** + topologia Docker pedida → **Q35 aprovado**: Laravel 11/PHP 8.3, Blade + Alpine, PostgreSQL (pgvector), Mailpit, dompdf; instruções de instalação do Docker Desktop emitidas ao time (diário, sessão 15).

### C-28 · 20/10 23:11

> **Prompt do time (texto original):**
>
> docker instalado, dá play e manda bala

**Resultado:** Scaffold no ar: Laravel gerado em contêiner, imagem `presenza/php` (PHP 8.4), composes dev/homolog, `.env`, migrations base, Breeze/dompdf/qrcode, assets sem build, Boost e smoke 200 na home e no `/login` (diário, sessão 16).

### C-29 · 20/10 23:42

> **Prompt do time (texto original):**
>
> tá pronto?

**Resultado:** Infra e domínio 100% prontos; telas do caminho crítico (login, termo, verificação pública com QR, presença facial) em andamento, dentro do prazo (diário, sessão 18).

### C-30 · 21/10 00:07

> **Prompt do time (texto original):**
>
> esses testes não é melhor serem escritos com um framework de testes? ou continua do jeito que está? você pode encontrar skills que te ajude a desenvolver essa aplicação com o Laravel? se não, continua do jeito que está, mas assim, não me parece muito eficiente a forma que está sendo feito, a menos que você tenha uma sugestão melhor

**Resultado:** Observação aceita: os `curl` ad-hoc viraram **suíte PHPUnit** no PostgreSQL real (pgvector não existe no SQLite), com `laravel-best-practices` e `testing-best-practices` do Boost → **49 testes / 144 asserções** (diário, sessão 19).

### C-31 · 21/10 00:27

> **Prompt do time (texto original):**
>
> segue com o desenvolvimento

**Resultado:** Bloco RH implementado com testes: tickets, justificativas, certificados (emissão/reemissão), relatórios CSV e escopo por papel → **66 testes / 209 asserções** (diário, sessão 20).

### C-32 · 21/10 01:38

> **Prompt do time (texto original):**
>
> adicione no seed vários dados de exemplo, pode encher

**Resultado:** `DadosExtrasSeeder` (SEED04) criado sem tocar nos cenários de demo: +2 setores, +13 pessoas com biometria de fachada, +6 motivos, +5 treinamentos, +15 aulas, filas, tickets e uma **reemissão real** de certificado (diário, sessão 29).

### C-33 · 21/10 01:57

> **Prompt do time (texto original):**
>
> vê o que alterei, documente onde precisar e commit

**Resultado:** Impacto do renome dos composes mapeado (include quebrado, default agora = homologação), include corrigido, docs sincronizadas e commit feito (diário, sessão 30).

### C-34 · 21/10 02:06

> **Prompt do time (texto original):**
>
> se eu upar o docker-compose.yaml agora vai funcionar normal a homologação? vai rodar os seeds?

**Resultado:** Resposta separando as duas coisas: **subir** funciona (config validado, pilha de pé) e o **seed não** rodaria sozinho — a IA propôs a política de seed no `up` para aprovação (AT01) (diário, sessão 31).

### C-35 · 21/10 02:07

> **Prompt do time (texto original):**
>
> quero que rode o seed quando eu subir o @docker-compose.yaml

**Resultado:** Duas políticas apresentadas (reset total vs. só banco vazio) com recomendação; o time aprovou o **reset total** → `migrate:fresh --seed --force` no comando do `app` (diário, sessão 31).

### C-36 · 21/10 03:14

> **Prompt do time (texto original):**
>
> olha, faça uma revisão no layout, principalmente espaçamentos e intuitividade, também melhore a navegação, adicionando botões de voltar para páginas anteriores, e menus laterais claros e intuitivos para navegação entre as telas

**Resultado:** **App shell** novo: sidebar por papel, botão Voltar em 10 telas, ícones SVG únicos, ritmo de espaçamento 4/8, skip-link; validado em 3 níveis (testes, smoke autenticado, screenshots) (diário, sessão 36).

### C-37 · 21/10 03:55

> **Prompt do time (texto original):**
>
> problemas de telas: em algumas páginas, há elementos como botões e inputs muito colados, como na /verificar, textos muito colados na /login (nessa deixe a lista de e-mails e a senha numa listinha mais bem feita), há também diversos lugares com caracteres errados, como "UsuÃ¡rios" presente em /portal/rh/usuarios, alguns inputs com valores, para as listas, eu quero algo mais próximo de tabelas com pesquisa para facilitar encontrar os dados que desejo

**Resultado:** Causa do **mojibake** isolada (2 arquivos com dupla codificação UTF-8), login repaginado com lista de contas e **busca client-side** em Usuários/Matriz/Tickets/Justificativas (diário, sessão 37).

### C-38 · 21/10 04:15

> **Prompt do time (texto original):**
>
> com qual comando eu consigo startar o server em dev?

**Resultado:** Comandos de dev devolvidos (compose de serviços + contêiner one-off com `artisan serve` na 8000), com a topologia de demonstração mantida de pé (diário, sessão 37).

### C-39 · 21/10 04:19

> **Prompt do time (texto original):**
>
> roda aí pra mim testar

**Resultado:** Servidor de dev subido em paralelo na `:8000` contra o mesmo PostgreSQL, sem derrubar a demonstração (diário, sessão 38).

### C-40 · 21/10 04:26

> **Prompt do time (texto original):**
>
> Mantenha apenas o de dev ativo por enquanto, em paralelo vai fazendo as correções:
> Na tela de login, facilite para que os usuários possam possam simplesmente copiar uma vez o e-mail inteiro pra testar as contas em cada caso, sem separar o e-mail em partes, e troque o e-mail dessas contas por @exemplo.com. Remova esses códigos sem sentido pelo código como RF** e por aí vai, tem que ser algo amigável pros usuários que forem usar a plataforma

**Resultado:** Domínio de e-mail trocado para `@exemplo.com`, e-mail completo copiável em um clique na tela de login e códigos **RF** removidos da interface (diário, sessão 38).

### C-41 · 21/10 04:26

> **Prompt do time (texto original):**
>
> também padronize os estilos de botões

**Resultado:** Família única de botões (`.btn`), com fonte herdada e semântica por cor (primário / acento / outline) (diário, sessão 38).

### C-42 · 21/10 04:27

> **Prompt do time (texto original):**
>
> os códigos estranhos inclui os Q* também

**Resultado:** Varredura ampliada para os códigos `Q*` além dos `RF*` — a nomenclatura interna de documentação some da interface (diário, sessão 38).

### C-43 · 21/10 04:28

> **Prompt do time (texto original):**
>
> remova mensagens que são pros desenvolvedores, como na tela de login que tem um " a biometria aqui NÃO é usada (só para validar presença, Q10).", isso não faz sentido algum pra apresentação

**Resultado:** Notas de desenvolvedor removidas das telas (ex.: “a biometria aqui NÃO é usada (só para validar presença, Q10)”) e textos reescritos em linguagem de uso (diário, sessão 38).

### C-44 · 21/10 10:58

> **Prompt do time (texto original):**
>
> o reconhecimento facial tá funcionando? resposta simples e rápida

**Resultado:** Resposta direta: o facial roda em **modo simulador** (fachada, RNF06) — sem câmera ou hardware; o Docker Desktop tinha caído e foi reerguido por instrução ao time (AT03) (diário, sessão 40).

### C-45 · 21/10 11:04

> **Prompt do time (texto original):**
>
> # ParseError - Internal Server Error
> 
> syntax error, unexpected token "elseif", expecting end of file
> 
> PHP 8.4.26
> Laravel 13.38.0
> localhost:8000
> 
> ## Stack Trace
> 
> 0 - resources/views/portal/aula.blade.php:24
> 1 - vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php:124
> 2 - vendor/laravel/framework/src/Illuminate/View/Engines/PhpEngine.php:57
> 3 - vendor/laravel/framework/src/Illuminate/View/Engines/CompilerEngine.php:76
> 4 - vendor/laravel/framework/src/Illuminate/View/View.php:208
> 5 - vendor/laravel/framework/src/Illuminate/View/View.php:191
> 6 - vendor/laravel/framework/src/Illuminate/View/View.php:160
> 7 - vendor/laravel/framework/src/Illuminate/Http/Response.php:82
> 8 - vendor/laravel/framework/src/Illuminate/Http/Response.php:40
> 9 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:951
> 10 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:918
> 11 - vendor/laravel/framework/src/Illuminate/Routing/Router.php:833
> 12 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:180
> 13 - app/Http/Middleware/ExigirAceiteTermo.php:20
> 14 - vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:219
> 15 - vendor/laravel/boost/src/Middleware/InjectBoost.php
> 
> *(trecho condensado pelo entregável: o prompt original colou o stack trace completo — 64 frames — do `ParseError`; o cabeçalho do erro, a requisição `GET /portal/aulas/3`, a rota `ChamadaController@aula` e o arquivo `resources/views/portal/aula.blade.php:24` estão íntegros acima)*

**Resultado:** ParseError identificado em `aula.blade.php:24` (um `@if` engolido por edição anterior) → corrigido, com `php -l` e suíte **93/305 verde** (diário, sessão 40).

### C-46 · 21/10 11:10

> **Prompt do time (texto original):**
>
> faça com que todos os usuários no seed já tenham aceitado os termos da LGPD

**Resultado:** Aceite do termo passou a ser criado no `CatalogoSeeder` (depois do termo ativo) com `INSERT … SELECT … ON CONFLICT DO NOTHING` → **44/44 contas** com aceite; login vai direto ao portal (diário, sessão 40).

### C-47 · 21/10 11:39

> **Prompt do time (texto original):**
>
> o reconhecimento facial está apenas simulado para a aplicação? seria muito trabalhoso adicionar o reconhecimento facial de verdade?

**Resultado:** Resposta: hoje o facial é **simulado** (fachada com embeddings fake no pgvector); reconhecimento real exigiria provedor, câmera e coleta de embeddings — planejado como evolução pós-hackathon (RF03/RNF06 mantidos).


## Sessão Skill global para buscar e corrigir bugs

- **Janela:** 21/10
- **Diretório do agente:** `C:/Users/dimas/OneDrive/Fatec/hackaton`
- **Escopo:** Projeto do hackathon

### C-48 · 21/10 04:00

> **Prompt do time (texto original):**
>
> busque por uma skill e adicione ela globalmente para:
> encontrar e corrigir bugs eficientemente

**Resultado:** Busca de skill de debugging no ecossistema e escolha da candidata mais confiável (diário, sessões 37–38).

### C-49 · 21/10 04:03

> **Prompt do time (texto original):**
>
> tem alguma que serve pra procurar bugs no projeto?

**Resultado:** `getsentry/skills@find-bugs` (fonte oficial Sentry) indicada como melhor match para busca proativa de bugs no projeto (diário, sessão 37).


## Sessão Planejamento de assistente de IA no Presenza

- **Janela:** 21/10
- **Diretório do agente:** `C:/Users/dimas/OneDrive/Fatec/hackaton`
- **Escopo:** Projeto do hackathon

### C-50 · 21/10 11:14

> **Prompt do time (texto original):**
>
> acha que dá pra planejar colocar um assistente de IA usando o OpenCode e um modelo gratuito nesse sistema? planeje e me pergunte coisas para elaborar os requisitos até que não restem dúvidas sobre

**Resultado:** Planejamento de um assistente de IA dentro do Presenza (OpenCode + modelo gratuito) iniciado, com perguntas de requisitos para eliminar dúvidas — **em aberto para a equipe** (diário, sessão 41).

### C-51 · 21/10 11:40

> **Prompt do time (texto original):**
>
> opa, refaça as perguntas

**Resultado:** Rodada de perguntas do planejamento refeita com enunciados mais claros e opções — o requisito do assistente de IA segue **em aberto** para a equipe (diário, sessão 41).


## Sessão Correção do PDF de prompts e extração das conversas

- **Janela:** 21/10
- **Diretório do agente:** `C:/Users/dimas/OneDrive/Fatec/hackaton`
- **Escopo:** Projeto do hackathon

### C-52 · 21/10 11:27

> **Prompt do time (texto original):**
>
> o nome do João Marcelo está com o Marcelo cortado em "João Marcel dos Santos Silva" no docs/prompts-ia.pdf. Inclua nele também os prompts relevantes das conversas que conseguir pegar, é importante

**Resultado:** Nome corrigido em todos os artefatos (capa do PDF, README, relatório final e diário) e **PDF de prompts refeito** com o anexo dos prompts extraídos das conversas (diário, sessão 42).

### C-53 · 21/10 11:37

> **Prompt do time (texto original):**
>
> tente novamente

**Resultado:** Nova tentativa após a falha de leitura do PDF pelo modelo — a extração foi feita direto do banco de sessões do agente (SQLite), sem depender da leitura do PDF (diário, sessão 42).

---

## Como ler este anexo

- **C-xx** = prompt original do time, na ordem cronológica das conversas recuperadas.
- Prompts de confirmação muito curtos (“blz, escolheremos a 1”, “continue”, “roda aí pra mim testar”) aparecem também — a completude do processo é o quesito avaliado, e eles dão contexto às decisões subsequentes.
- Duas sessões pertencem a **outro repositório** (curadoria de skills e pendências de autenticação): foram mantidas por transparência, marcadas como fora do pacote do hackathon.
- A versão **polida** dos mesmos prompts — reescrita no formato contexto → objetivo → restrições → formato de saída, com resultado — está no **ATO 2 §6** do `docs/relatorio-final.md` (`P-01`…`P-41`).

*Documento gerado por extração automática do histórico de conversas do agente (skill `registro-hackathon`, AT04).*
