# 🗂️ Journey Log (Diário de Bordo) — Hackathon FATEC & ETEC · Usina Lins

> Histórico cronológico de tudo o que fizemos com IA. Cada sessão é numerada
> (Nº) e mantida em ordem de data/hora. Editar entradas antigas deve ser
> evitado, mas é permitido quando tornar a história do projeto mais clara
> para a apresentação à banca. Último registro no rodapé.

| Projeto escolhido                                                           | Integrantes | Início     | Status       |
| --------------------------------------------------------------------------- | ----------- | ---------- | ------------ |
| **P1 — Gestão de Treinamentos Internos (RH)** (decidido 20/10/15:33)         | —           | 20/10/2026 | em andamento |

---

## 1 · DOCUMENTAÇÃO · Leitura e interpretação dos 3 problemas do desafio

- **Quando:** 20/10/2026 14:50
- **O que foi feito:**
  - Extração do conteúdo do `problemas.docx` (Hackathon FATEC & ETEC, parceiros Usina Lins e CetaCeo);
  - Mapeamento dos problemas: **P1** Gestão de Treinamentos Internos (RH), **P2** Controle de Refeitório (~600 refeições/dia, 3 turnos), **P3** Controle de Visitas Externas (segurança patrimonial);
  - Registro dos critérios de avaliação: criatividade, clareza/completude do prompt, arquitetura + design de UI, uso de mocks.
- **Artefatos:** `problemas.docx`
  - (fonte lida; conteúdo iconografado na conversa).
- **Decisões / por quê:** entender primeiro o escopo de cada problema _antes_ de qualquer escolha de tecnologia, conforme a estratégia do time.
- **Prompts-chave:** IA instruída a extrair texto do .docx e estruturar os 3 problemas com atores e dores.
- **Status:** Concluído.

## 2 · ARQUITETURA · Estruturação da arquitetura conceitual dos 3 problemas

- **Quando:** 20/10/2026 14:55
- **O que foi feito:**
  - Definição de uma espinha dorsal comum: portais por ator → serviços de domínio → dados → fachada de integração de hardware, com camada transversal de identidade/LGPD/biometria/auditoria;
  - P1: arquitetura de ciclo completo (catálogo → sessões → reserva pelo líder → chamada facial → certificado com código de verificação → **portal público de verificação**), com/wiki de principais entidades;
  - P2: arquitetura em torno do **evento de consumo** (catraca facial, 1 entrada por janela de refeição, eventos → rateio empresa×funcionário, cardápio online, totem de feedback anônimo);
  - P3: arquitetura de convites: link de uso único com expiração → auto-cadastro do visitante → aprovação da segurança → provisionamento com janela de validade que **expira sozinha** nos leitores;
  - Comparativo dos 3 (complexidade, risco LGPD, hardware, "wow", viabilidade em 24h) para decidir o projeto.
- **Artefatos:** — (estrutura na conversa; será consolidada em doc ao decidir o projeto).
- **Decisões / por quê:** biometria e hardware (câmera, catraca, cancela) sempre atrás de fachada única com **modo simulador**, para a demo não depender de reconhecimento real funcionando na hora da banca.
- **Prompts-chave:** IA orientada a levantar arquitetura **sem tecnologias**, com escopo, atores, entidades e MVP.
- **Status:** Concluído (aguardando escolha do problema pelo time).

## 3 · CONFIGURAÇÃO · Criação da skill `registro-hackathon` e deste arquivo

- **Quando:** 20/10/2026 14:56
- **O que foi feito:**
  - Criação da skill em `.opencode/skills/registro-hackathon/SKILL.md`: define quando registrar, formato das entradas, categorias, regras de estilo e verificações;
  - Criação deste `journey-log.md` como histórico oficial do processo.
- **Artefatos:** `.opencode/skills/registro-hackathon/SKILL.md`, `journey-log.md`
- **Decisões / por quê:** a apresentação precisa de evidência cronológica do uso de IA; o registro é escrito por IA ao fim de cada sessão, sem retrabalho para o time.
- **Prompts-chave:** pedido do time: "faça uma skill que a cada operação/documentação/discussão com IA você adicione um resumo em ordem e quando".
- **Status:** Concluído.

## 4 · DECISÃO · Identidade unificada: reconhecimento facial como primário + login/senha como fallback

- **Quando:** 20/10/2026 15:05
- **O que foi feito:**
  - Definição global para os 3 projetos: **reconhecimento facial é o meio principal** de identificação do usuário (time já tem experiência com o método);
  - **Fallback: login/senha convencional**, vinculado ao mesmo cadastro do usuário que carrega a biometria;
  - Banco escolhido: **PostgreSQL** (versatilidade para todos os cenários);
  - Solicitado o mapeamento da estrutura de dados de reconhecimento facial (credenciais biométricas, foto de referência, assinatura negative dependendo do provedor, consentimento e log de eventos).
- **Artefatos:** (planejamento consolidado na conversa).
- **Decisões / por quê:** simplicidade operacional e confiabilidade: biometria sempre como padrãoização, login/senha como via alternativa caso indisponível.
- **Prompts-chave:** diretiva do time consolidando: biometria facial primária, login/senha fallback, PostgreSQL.
- **Resultados:** plano atualizado com modelo de armazenamento de dados biométricos.
- **Status:** Concluído.

## 5 · CONFIGURAÇÃO · Versionamento Git + reforço na skill de registro

- **Quando:** 20/10/2026 15:10
- **O que foi feito:**
  - Inicialização/normalização do repositório Git (branch `main`) e `.gitignore` (locks Office, `.env`, temporários do OpenCode);
  - Commit inicial: `chore: init repo — problemas, registro de sessões do hackathon e skill registro-hackathon`;
  - Atualização da skill `registro-hackathon`: agora **puxa e refina os prompts do time antes de registrar** (contexto → objetivo → restrições → formato saída, preservando o sentido) e documenta os **resultados** de cada uso;
  - Preparado campo para sincronização com GitHub (aguardando URL do repositório remoto).
- **Artefatos:** `.opencode/skills/registro-hackathon/SKILL.md`, `.gitignore`, `journey-log.md`
- **Decisões / por quê:** versionamento do processo e dos prompts para a entrega em PDF; clareza dos prompts é critério avaliado pela banca.
- **Prompts-chave:** solicitação para todo prompt enviado ser polido e ter resultados documentados; alteração registrada na skill.
- **Resultados:** skill ampliada; repositório versionado.
- **Status:** Concluído (push ao GitHub pendente de URL do remote).

## 6 · CONFIGURAÇÃO · Renomeio para `journey-log.md` e flexibilização da regra de edição

- **Quando:** 20/10/2026 15:13
- **O que foi feito:**
  - Renomeação do arquivo de registro para **`journey-log.md`** (termo em inglês para "diário de bordo", a pedido do time), com `git mv` para preservar o rastreio de histórico;
  - Ajuste na skill `registro-hackathon` e no próprio cabeçalho deste log: edição e correção de entradas antigas deixou de ser proibida absoluta — **evita-se, porém é permitido** quando ajudar a apresentar melhor a história do projeto à banca (preservados sempre os Nº e a ordem cronológica).
- **Artefatos:** `journey-log.md`, `.opencode/skills/registro-hackathon/SKILL.md`
- **Decisões / por quê:** nomenclatura mais forte/daora para o artefato de apresentação; histórico útil vale mais que histórico "congelado", desde que mantida a integridade cronológica.
- **Prompts-chave:** pedido para renomear o arquivo com termo em inglês referente a diário de bordo e remover a regra de nenhum retrabalho permitido.
- **Resultados:** nome e política de edição atualizados; sem perda de histórico anterior.
- **Status:** Concluído.

## 7 · DISCUSSÃO · Recomendação: escolher o Problema 1 (Treinamentos)

- **Quando:** 20/10/2026 15:29
- **O que foi feito:** análise rápida dos 3 cenários para escolha do desafio.
- **Artefatos:** —
- **Decisões / por quê:** **P1** recomendado: menor dependência de hardware real (só câmera na chamada), MVP factível no prazo, e o "wow" da demo é acessível de celular — o avaliador valida um certificado ao vivo na página pública de verificação. **P3** é o plano B (fluxo de aprovação + expiração visível). **P2** é o mais rico, mas o mais arriscado (catraca física + rateio complexo).
- **Prompts-chave:** pedido de recomendação em poucas palavras para a escolha.
- **Resultados:** decisão pendente de confirmação do time.
- **Status:** Parcial — aguardando decisão final do time.

## 8 · DECISÃO · Problema 1 escolhido + documento-base de arquitetura

- **Quando:** 20/10/2026 15:33
- **O que foi feito:**
  - Time travou o **Problema 1 (Gestão de Treinamentos Internos — RH)** como desafio do hackathon;
  - Criação do documento-base `docs/arquitetura-p1-treinamentos.md`: camadas, atores, modelo de dados PostgreSQL (`credencial_biometrica` com provedor/versão do extractor, `certificado` imutável com hash, `log_verificacao_publica`), fluxos (chamada facial com fallback login/senha), regras anti-fraude, telas para mockups e MVP;
  - Quadro do cabeçalho deste log atualizado com a escolha.
- **Artefatos:** `docs/arquitetura-p1-treinamentos.md`, `journey-log.md`
- **Decisões / por quê:** P1 = maior viabilidade em 24h + "wow" verificável ao vivo na banca; fallback login/senha cobre falha de biometria em produção e na demo.
- **Prompts-chave:** confirmação da escolha ("blz, escolheremos a 1") com instrução de prosseguir no planejamento.
- **Resultados:** próximo passo acordado: mockups/telas e esqueleto do projeto gerados a partir deste documento.
- **Status:** Concluído.

---

_Última atualização: 20/10/2026 15:33 — gerado pela skill `registro-hackathon`._
