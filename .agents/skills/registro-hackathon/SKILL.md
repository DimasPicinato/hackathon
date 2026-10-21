---
name: registro-hackathon
description: Documenta em journey-log.md, de forma resumida e cronológica, toda discussão, decisão, documentação ou trabalho feito com IA neste repositório (hackathon FATEC/ETEC Usina Lins). Além disso, faz o polimento de quase todo prompt enviado pelo time (legibilidade/estrutura) e documenta os resultados obtidos, e mantém o relatório de entrega docs/relatorio-final.md sempre sincronizado com o que foi feito. Use ao final de qualquer tarefa concluída, quando o usuário pedir "registre isso", "documente no registro", "polir esse prompt", ou tiver criado/alterado artefatos do projeto.
---

# Skill: Registro de Sessões do Hackathon

Propósito: manter um histórico contínuo e verificável do processo de desenvolvimento com IA, para apresentar à banca do Hackathon (quesitos de clareza/completude dos prompts e evidência do processo criativo).

## Quando registrar

Registre ao final de uma interação quando houver **qualquer** destes resultados:

- uma decisão tomada (arquitetura, escolha de problema, regra de negócio);
- artefatos criados ou alterados (código, docs, prompts, diagramas, planos);
- uma discussão que produza conclusão relevante (ex.: leitura/interpretação dos problemas);
- um prompt/modelo de prompt importante usado com a IA.

NÃO registre: interações triviais (perguntar hora, erro de digitação, teste abandonado).

## Polimento de prompts do time

Quase todo prompt que o time enviar (na conversa, para reuso com outras IAs ou para o PDF de entrega) deve passar por **polimento antes de ser registrado/arquivado**:

1. **Reestruture para legibilidade**: contexto → objetivo → restrições → formato de saída esperado, com marcadores claros.
2. **Preserve o sentido original**: polimento é clareza, não reescrita de conteúdo. Se houver ambiguidade no prompt original, pergunte em vez de supor.
3. **Corrija ruídos comuns**: repetições, jargão desnecessário, objetivos misturados em um parágrafo só, pé de texto quebrado.
4. Ao final da sessão em que um prompt polido foi usado, documente o **resultado** na entrada do registro (campo `Resultados`): o que a IA entregou e o que o time aproveitou/descartou. Isso alimenta diretamente o PDF de prompts que a banca exige.
5. Se o time pedir, salve a versão polida em `prompts/` com nome descritivo (ex.: `prompts/2026-10-20-arquitetura-p1.md`), com frontmatter curto: data, autor do prompt original, objetivo.

## Como registrar

1. **Garanta o arquivo** `journey-log.md` na raiz do repositório. Se não existir, crie-o com o cabeçalho padrão (ver template abaixo).
2. **Obtenha a data/hora local** (ferramenta de shell) para o campo `Quando` — nunca invente horário.
3. **Abra o arquivo e identifique o próximo número de sessão** (o último Nº existente + 1). Se o histórico tiver sido compactado, use o maior número existente + 1.
4. **Apende a nova entrada NO FINAL do arquivo** (histórico em ordem cronológica, mais antigo primeiro). Editar entradas antigas deve ser **evitado**, mas **é permitido** quando tornar o histórico mais claro e melhores apresentáveis à banca (ex.: corrigir erro factual, consolidar repetição do mesmo dia, enriquecer uma decisão). Preserve sempre os números Nº e a cronologia geral; se editar, regere o rodapé.
5. Se houver uma sessão anterior _muito_ similar no mesmo dia, o usuário pode pedir fundição ("festival de entradas pequenas") — só compacta sob pedido explícito, mantendo o inteiro de datas/horas que corresponda.
6. Se o usuário disser "não registre", "ignore este log" ou equivalente: pule o registro silenciosamente.

## Formato da entrada

```markdown
## Nº <sequencial> · <CATEGORIA> · <título curto, sem jargão>

- **Quando:** <dd/MM/yyyy HH:MM>
- **O que foi feito:**
  - <2 a 5 bullets, imperativo ou parágrafo curto, focado no resultado>;
- **Artefatos:** `<caminho/relativo>`
  - (link relativo para os arquivos criados/lidos relevantes; "—" se só discussão);
- **Decisões / por quê:** <uma ou duas frases justificando; "—" se puro trabalho operacional>;
- **Prompts-chave:** resumo de 1 linha sobre como a IA foi usada (importante para o PDF de prompts exigido); se houver versão polida, referencie o caminho em `prompts/`;
- **Resultados:** o que a saída da IA produziu e o que foi aproveitado (1 linha; "—" se não se aplica);
- **Status:** Concluído · Parcial · Pendente decisão.
```

Categorias válidas (exatamente uma por entrada): `ARQUITETURA` · `CÓDIGO` · `CONFIGURAÇÃO` · `DECISÃO` · `DOCUMENTAÇÃO` · `DISCUSSÃO` · `PROMPT` · `PESQUISA` · `TESTE`.

## Cabeçalho do arquivo (usar ao criar; manter atualizado)

No topo do `journey-log.md`, mantenha:

```markdown
# 🗂️ Journey Log (Diário de Bordo) — Hackathon FATEC & ETEC · Usina Lins

> Histórico cronológico de tudo o que fizemos com IA. Cada sessão é numerada
> (Nº) e em ordem de data/hora. Evita-se editar entradas antigas, mas
> correções/complementos são permitidos quando ajudarem a contar melhor a
> história do projeto à banca. Último registro no rodapé.

| Projeto escolhido                                                           | Integrantes | Início     | Status       |
| --------------------------------------------------------------------------- | ----------- | ---------- | ------------ |
| (P1 Treinamentos · P2 Refeitório · P3 Visitas — preencher quando decidirem) |             | 20/10/2026 | em andamento |
```

E ao rodapé do arquivo, mantenha sempre:

```markdown
---

_Última atualização: <dd/MM/yyyy HH:MM> — gerado pela skill `registro-hackathon`._
```

## Regras de estilo

- Resumido: entrada inteira ≤ ~10 linhas de markdown.
- Sem citar nomes de ferramentas/tecnologias nas decisões arquiteturais, a menos que o usuário tenha escolhido explicitamente a tecnologia.
- Escrever em português (padrão da equipe).
- Não citar nomes de modelos de IA; dizer "IA usada para X" (o PDF de prompts ficará a cargo do grupo).
- Se o usuário pedir para revisar/compactar o registro inteiro, sempre preserve a ordem cronológica original e o seu rótulo Nº.

## Manutenção do relatório final de entrega (`docs/relatorio-final.md`)

`docs/relatorio-final.md` é o documento-base do **PDF exigido pela banca** (todos os prompts + processo). Ele **evolui junto com o projeto**: a IA o mantém atualizado em toda sessão registrada, **sem esperar pedido explícito** do time. Não é opcional:journey-log e relatório andam sempre juntos.

Regras por tipo de mudança:

- **Nova sessão de trabalho** (código, configuração, decisão, discussão, pesquisa, teste) → além de registrar `Nº+1` no `journey-log.md`, atualize o relatório:
  - **§6 (Prompts do desenvolvimento):** acrescente o prompt desta sessão **polido** (contexto → objetivo → restrições → formato), com o **próximo número da sequência** (P-40, P-41, …) e o **resultado** obtido;
  - **§9 (Diário de bordo):** acrescente o resumo da nova sessão (mesma informação do journey-log, condensada);
  - **§8 (Passos):** inclua o passo na cronologia executiva, se aplicável.
- **Mudança em requisitos, design, stack, acordos, deploy ou demo** → sincronize também **§3, §4, §5, §10** conforme o caso.
- **Mudança em números/estado** (testes/asserções, RFs fechados, credenciais, códigos de verificação, URL, contagem de sessões) → atualize o **ATO 1** (§1.4, §1.5) e o **§10** — nunca deixe dado defasado no documento que vira PDF.
- **Estilo:** edite pontualmente as seções afetadas; **preserve a numeração** (ATO/§/P-xx/Nº), a estrutura e o tom do documento. Não reescreva o que já está aprovado.
- **Prompts:** a menos que o time peça outra coisa, os prompts polidos ficam registrados no **§6 do relatório** (o `prompts/` continua opcional para reuso externo).

## Verificações antes de terminar

- [ ] Novo Nº é exatamente o maior Nº antigo + 1.
- [ ] Entrada está após a última com a mesma data ou depois (ordem cronológica respeitada).
- [ ] Links de artefatos são relativos e de fato existem.
- [ ] `docs/relatorio-final.md` sincronizado com esta sessão (§6 prompt novo com resultado, §9 diário, §8 passos, números do ATO 1/§10 quando mudarem).
- [ ] Rodapé "Última atualização" foi regerado.
