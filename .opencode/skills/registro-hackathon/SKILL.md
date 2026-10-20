---
name: Registro Hackathon
description: Documenta em registro-sessoes.md, de forma resumida e cronológica, toda discussão, decisão, documentação ou trabalho feito com IA neste repositório (hackathon FATEC/ETEC Usina Lins). Use ao final de qualquer tarefa concluída, quando o usuário pedir "registre isso", "documente no registro", ou tiver criado/alterado artefatos do projeto.
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

## Como registrar

1. **Garanta o arquivo** `registro-sessoes.md` na raiz do repositório. Se não existir, crie-o com o cabeçalho padrão (ver template abaixo).
2. **Obtenha a data/hora local** (ferramenta de shell) para o campo `Quando` — nunca invente horário.
3. **Abra o arquivo e identifique o próximo número de sessão** (o último Nº existente + 1). Se o histórico tiver sido compactado, use o maior número existente + 1.
4. **Apende a nova entrada NO FINAL do arquivo** (histórico em ordem cronológica, mais antigo primeiro — nunca adicione no meio, nunca reordene, nada se apaga).
5. Se houver uma sessão anterior *muito* similar no mesmo dia, o usuário pode pedir fundição ("festival de entradas pequenas") — só compacta sob pedido explícito, mantendo o inteiro de datas/horas que corresponda.
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
- **Prompts-chave:** resumo de 1 linha sobre como a IA foi usada (importante para o PDF de prompts exigido);
- **Status:** Concluído · Parcial · Pendente decisão.
```

Categorias válidas (exatamente uma por entrada): `ARQUITETURA` · `CÓDIGO` · `CONFIGURAÇÃO` · `DECISÃO` · `DOCUMENTAÇÃO` · `DISCUSSÃO` · `PROMPT` · `PESQUISA` · `TESTE`.

## Cabeçalho do arquivo (usar ao criar; manter atualizado)

No topo do `registro-sessoes.md`, mantenha:

```markdown
# 🗂️ Registro de Sessões — Hackathon FATEC & ETEC · Usina Lins

> Histórico cronológico de tudo o que fizemos com IA. Cada sessão é numerada e
> nunca é editada retroativamente. Último registro no rodapé.

| Projeto escolhido | Integrantes | Início | Status |
|---|---|---|---|
| (P1 Treinamentos · P2 Refeitório · P3 Visitas — preencher quando decidirem) | | 20/10/2026 | em andamento |
```

E ao rodapé do arquivo, mantenha sempre:

```markdown
---
*Última atualização: <dd/MM/yyyy HH:MM> — gerado pela skill `registro-hackathon`.*
```

## Regras de estilo

- Resumido: entrada inteira ≤ ~10 linhas de markdown.
- Sem citar nomes de ferramentas/tecnologias nas decisões arquiteturais, a menos que o usuário tenha escolhido explicitamente a tecnologia.
- Escrever em português (padrão da equipe).
- Não citar nomes de modelos de IA; dizer "IA usada para X" (o PDF de prompts ficará a cargo do grupo).
- Se o usuário pedir para revisar/compactar o registro inteiro, sempre preserve a ordem cronológica original e o seu rótulo Nº.

## Verificações antes de terminar

- [ ] Novo Nº é exatamente o maior Nº antigo + 1.
- [ ] Entrada está após a última com a mesma data ou depois (ordem cronológica respeitada).
- [ ] Links de artefatos são relativos e de fato existem.
- [ ] Rodapé "Última atualização" foi regerado.
