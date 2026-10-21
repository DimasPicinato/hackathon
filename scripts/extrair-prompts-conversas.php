<?php

/**
 * Extração dos prompts ENVIADOS PELO TIME a partir do histórico de conversas
 * do agente OpenCode (banco SQLite de sessões) — evidência bruta para o PDF de
 * prompts exigido pela banca.
 *
 * Uso (dentro do contêiner, com o banco do agente montado):
 *   docker run --rm -v .:/app -w /app \
 *     -v "$HOME/.local/share/opencode:/data:ro" \
 *     presenza/php php scripts/extrair-prompts-conversas.php /data/opencode.db docs/prompts-conversas.md
 *
 * Saída: Markdown com os prompts na íntegra (verbatim) + o resultado obtido em
 * cada um, agrupados por sessão e em ordem cronológica.
 */

$dbPath = $argv[1] ?? (getenv('OC_DB') ?: '');
$outPath = $argv[2] ?? 'docs/prompts-conversas.md';

if ($dbPath === '' || ! file_exists($dbPath)) {
    fwrite(STDERR, "Uso: php scripts/extrair-prompts-conversas.php <caminho/opencode.db> [saida.md]\n");
    exit(1);
}

$pdo = new PDO('sqlite:'.$dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$tz = new DateTimeZone('America/Sao_Paulo');

/** Sessões do agente OpenCode recuperadas (id, rótulo, escopo) */
$sessoes = [
    'ses_eaab1bdb3ffe0CTUDyXMyFNB6Y' => ['Controle de Skills / autenticação (outro repositório)', '19–20/10'],
    'ses_ea6477f76ffeM5qrksfAp89vIG' => ['Curadoria de skills do agente', '20/10'],
    'ses_ea594ad62ffdpuUmRiVmyvMijI' => ['Principal do hackathon (Presenza)', '20–21/10'],
    'ses_ea2bce658ffdRiE2TxsC2GDTF2' => ['Skill global para buscar e corrigir bugs', '21/10'],
    'ses_ea1300d8affdA54wdlANdDVpVl' => ['Planejamento de assistente de IA no Presenza', '21/10'],
    'ses_ea12c7298ffdsSZ18DoNY2J6oS' => ['Correção do PDF de prompts e extração das conversas', '21/10'],
];

/** Resultado de cada prompt — casados por um trecho único do texto original */
$resultados = [
    ['quero que me explique e estruture uma arquitetura', 'Arquitetura conceitual dos 3 problemas **sem tecnologias**, com escopo de cada um, extraída do `problemas.docx`; base comum (identidade, elegibilidade, biometria com via alternativa, auditoria, LGPD, fachada única de hardware) e comparativo de escopo/risco (diário, sessão 2).'],
    ['faça uma pequena skill nesse próprio repositório', 'Skill `registro-hackathon` + arquivo de registro criados, com 3 entradas iniciais e o campo **“Prompts-chave”** já pensando no PDF da banca (diário, sessão 3).'],
    ['para todo caso, os meios de reconhecimento do usuário', 'Skill ampliada (polimento de prompts + registro de resultados), Git inicializado com commit inicial, estrutura de armazenamento biométrico mapeada e **PostgreSQL** fixado; planejamento resumido reemitido (diário, sessões 4 e 5).'],
    ['altere o nome do arquivo da documentação para algo que remeta', '`registro-sessoes.md` → **`journey-log.md`** (via `git mv`, histórico preservado) e política de edição das entradas flexibilizada na skill e no cabeçalho (diário, sessão 6).'],
    ['agora vamos conversar brevemente em poucas palavras', 'Recomendação objetiva: **P1** (viabilidade em 24h, “wow” verificável pelo celular, risco LGPD controlado); P3 como plano B e P2 como o mais arriscado (diário, sessão 7).'],
    ['desde que iniciei o repositório, já dava pra fazer push', '`origin` já configurada (`github.com/DimasPicinato/hackaton`) e push confirmado pela IA; sessão registrada e commitada (diário, sessões 7–8).'],
    ['blz, escolheremos a 1', '**P1 travado.** A arquitetura detalhada foi aberta e depois revertida a pedido do time (preservada no histórico do Git, commit `c081de1`) — o passo seguinte virou levantamento de requisitos (diário, sessão 8).'],
    ['sem definir arquitetura ou nada ainda', 'Documento de arquitetura removido do pacote de trabalho (preservado no Git) e busca da skill de levantamento de requisitos feita — `grill-me`/`grilling` (mattpocock/skills) selecionadas (diário, sessão 9).'],
    ['como eu posso resolver o seu problema com o powershell', 'Causa do bloqueio identificada (ExecutionPolicy do PowerShell barrando `npx.ps1`) e comando de liberação entregue ao time — **instalação sempre instruída** (AT03) (diário, sessão 9).'],
    ['ae, autorizei, pode prosseguir', '`npx` liberado e `grill-me` instalado (≈1,4 mi de instalações); `grilling` escolhida para o questionário (diário, sessão 10).'],
    ['faça o questionário pelo opencode', 'Questionário interativo iniciado no próprio OpenCode, sempre com a opção recomendada em primeiro lugar (diário, sessão 10).'],
    ['não entendi as perguntas, deixe-as mais claras', 'Perguntas reescritas com situação concreta de exemplo em cada uma; rodada refeita (diário, sessão 10).'],
    ['continue', 'Rodada 3 do questionário refeita (o envio anterior falhou por formato) — **Q1–Q30** fechadas (diário, sessão 10).'],
    ['para confirmar presença, tanto o colaborador, quanto o instrutor', 'Ajuste incorporado ao requisito de presença: **facial autônomo** (colaborador) + **facial via instrutor** + **homologação manual** exigindo a senha do colaborador e um motivo (catálogo ou novo no ato) → RF14/RF15/RF27 (diário, sessão 10).'],
    ['então você acredita que consegue seguir muito bem', 'Confiança alta nos requisitos (ciclo completo e rastreável RF/RNF), 3 pendências externas mapeadas (SMTP, fotos para o seed, ensaio do simulador) e sequência de execução proposta (diário, sessão 11).'],
    ['crie um .md para armazenar todo o conteúdo desse questionário', 'Transcrição Q&A salva em `docs/questionario-requisitos-p1.md` e referenciada no cabeçalho dos requisitos e no diário (diário, sessão 10).'],
    ['vamos decidir uma paleta de cores', 'Paleta e tipografia decididas com o dataset da skill `ui-ux-pro-max` (**verde agro + laranja de segurança**, Lexend + Source Sans 3) e kit de skills de design fechado → `docs/design-p1.md` (diário, sessão 13).'],
    ['qual nome damos ao projeto', '5 candidatas com racional (Certia, TurmaCert, NRFlow, Assidu, **Presenza**) → o time escolheu **Presenza**, com a tagline “presenças reais, certificados à prova” (diário, sessão 14).'],
    ['ainda falta algo à definir', 'Confirmação de que não havia pendências de definição; as 3 pendências externas (SMTP do time, fotos dos integrantes, ensaio do simulador) ficaram documentadas como não bloqueantes (diário, sessões 11 e 13).'],
    ['vamos decidir a stack', 'Comparativo de stacks pelos critérios dos RFs + regras fixas **AT01/AT02/AT03** + topologia Docker pedida → **Q35 aprovado**: Laravel 11/PHP 8.3, Blade + Alpine, PostgreSQL (pgvector), Mailpit, dompdf; instruções de instalação do Docker Desktop emitidas ao time (diário, sessão 15).'],
    ['docker instalado, dá play e manda bala', 'Scaffold no ar: Laravel gerado em contêiner, imagem `presenza/php` (PHP 8.4), composes dev/homolog, `.env`, migrations base, Breeze/dompdf/qrcode, assets sem build, Boost e smoke 200 na home e no `/login` (diário, sessão 16).'],
    ['tá pronto?', 'Infra e domínio 100% prontos; telas do caminho crítico (login, termo, verificação pública com QR, presença facial) em andamento, dentro do prazo (diário, sessão 18).'],
    ['esses testes não é melhor serem escritos com um framework', 'Observação aceita: os `curl` ad-hoc viraram **suíte PHPUnit** no PostgreSQL real (pgvector não existe no SQLite), com `laravel-best-practices` e `testing-best-practices` do Boost → **49 testes / 144 asserções** (diário, sessão 19).'],
    ['segue com o desenvolvimento', 'Bloco RH implementado com testes: tickets, justificativas, certificados (emissão/reemissão), relatórios CSV e escopo por papel → **66 testes / 209 asserções** (diário, sessão 20).'],
    ['adicione no seed vários dados de exemplo', '`DadosExtrasSeeder` (SEED04) criado sem tocar nos cenários de demo: +2 setores, +13 pessoas com biometria de fachada, +6 motivos, +5 treinamentos, +15 aulas, filas, tickets e uma **reemissão real** de certificado (diário, sessão 29).'],
    ['vê o que alterei, documente onde precisar e commit', 'Impacto do renome dos composes mapeado (include quebrado, default agora = homologação), include corrigido, docs sincronizadas e commit feito (diário, sessão 30).'],
    ['se eu upar o docker-compose.yaml agora vai funcionar', 'Resposta separando as duas coisas: **subir** funciona (config validado, pilha de pé) e o **seed não** rodaria sozinho — a IA propôs a política de seed no `up` para aprovação (AT01) (diário, sessão 31).'],
    ['quero que rode o seed quando eu subir', 'Duas políticas apresentadas (reset total vs. só banco vazio) com recomendação; o time aprovou o **reset total** → `migrate:fresh --seed --force` no comando do `app` (diário, sessão 31).'],
    ['olha, faça uma revisão no layout', '**App shell** novo: sidebar por papel, botão Voltar em 10 telas, ícones SVG únicos, ritmo de espaçamento 4/8, skip-link; validado em 3 níveis (testes, smoke autenticado, screenshots) (diário, sessão 36).'],
    ['problemas de telas', 'Causa do **mojibake** isolada (2 arquivos com dupla codificação UTF-8), login repaginado com lista de contas e **busca client-side** em Usuários/Matriz/Tickets/Justificativas (diário, sessão 37).'],
    ['com qual comando eu consigo startar o server', 'Comandos de dev devolvidos (compose de serviços + contêiner one-off com `artisan serve` na 8000), com a topologia de demonstração mantida de pé (diário, sessão 37).'],
    ['roda aí pra mim testar', 'Servidor de dev subido em paralelo na `:8000` contra o mesmo PostgreSQL, sem derrubar a demonstração (diário, sessão 38).'],
    ['mantenha apenas o de dev ativo', 'Domínio de e-mail trocado para `@exemplo.com`, e-mail completo copiável em um clique na tela de login e códigos **RF** removidos da interface (diário, sessão 38).'],
    ['também padronize os estilos de botões', 'Família única de botões (`.btn`), com fonte herdada e semântica por cor (primário / acento / outline) (diário, sessão 38).'],
    ['os códigos estranhos inclui os Q*', 'Varredura ampliada para os códigos `Q*` além dos `RF*` — a nomenclatura interna de documentação some da interface (diário, sessão 38).'],
    ['remova mensagens que são pros desenvolvedores', 'Notas de desenvolvedor removidas das telas (ex.: “a biometria aqui NÃO é usada (só para validar presença, Q10)”) e textos reescritos em linguagem de uso (diário, sessão 38).'],
    ['o reconhecimento facial tá funcionando', 'Resposta direta: o facial roda em **modo simulador** (fachada, RNF06) — sem câmera ou hardware; o Docker Desktop tinha caído e foi reerguido por instrução ao time (AT03) (diário, sessão 40).'],
    ['parseerror - internal server error', 'ParseError identificado em `aula.blade.php:24` (um `@if` engolido por edição anterior) → corrigido, com `php -l` e suíte **93/305 verde** (diário, sessão 40).'],
    ['faça com que todos os usuários no seed já tenham aceitado', 'Aceite do termo passou a ser criado no `CatalogoSeeder` (depois do termo ativo) com `INSERT … SELECT … ON CONFLICT DO NOTHING` → **44/44 contas** com aceite; login vai direto ao portal (diário, sessão 40).'],
    ['o reconhecimento facial está apenas simulado', 'Resposta: hoje o facial é **simulado** (fachada com embeddings fake no pgvector); reconhecimento real exigiria provedor, câmera e coleta de embeddings — planejado como evolução pós-hackathon (RF03/RNF06 mantidos).'],
    ['observe as skills que você possui', 'Mapeamento das skills do projeto feito, proposta de remoção das redundantes e busca de skills de apoio (arquitetura, qualidade, clones) (diário, sessão 5).'],
    ['continuação do que eu estava escrevendo', 'Remoção da skill `design` redundante, instalação das 4 skills aprovadas no escopo do projeto e busca das skills populares de arquitetura/qualidade (diário, sessão 5).'],
    ['quero todas essas skills que está instalando em nível global', 'Skills instaladas também no **escopo global** do usuário (`~/.agents/skills/`), incluindo a Firecrawl que estava pendente (diário, sessão 5).'],
    ['quando eu disse que quero todas essas skills instaladas no global', 'Regra corrigida e aplicada: skill genérica (não acoplada ao contexto/arquitetura do repo) fica **também** no global; a `shadcn` permaneceu no projeto por ser adaptada ao repositório (diário, sessão 5).'],
    ['quero que a cópia local do projeto das skills globais', '7 cópias idênticas removidas do projeto (mantida apenas a global), com verificação de referências quebradas (diário, sessões 5 e 12).'],
    ['busque por uma skill e adicione ela globalmente', 'Busca de skill de debugging no ecossistema e escolha da candidata mais confiável (diário, sessões 37–38).'],
    ['tem alguma que serve pra procurar bugs', '`getsentry/skills@find-bugs` (fonte oficial Sentry) indicada como melhor match para busca proativa de bugs no projeto (diário, sessão 37).'],
    ['acha que dá pra planejar colocar um assistente de ia', 'Planejamento de um assistente de IA dentro do Presenza (OpenCode + modelo gratuito) iniciado, com perguntas de requisitos para eliminar dúvidas — **em aberto para a equipe** (diário, sessão 41).'],
    ['opa, refaça as perguntas', 'Rodada de perguntas do planejamento refeita com enunciados mais claros e opções — o requisito do assistente de IA segue **em aberto** para a equipe (diário, sessão 41).'],
    ['identifique o que ainda precisa ser feito em relação à autenticação', 'Inventário do que faltava em autenticação levantado **sem alterar nada** — sessão de outro repositório, mantida fora do pacote do hackathon.'],
    ['blz, complete todas essas fases', 'Plano de execução das fases fechado em modo de planejamento (sem implementação) — também de outro repositório, fora do escopo deste hackathon.'],
    ['o nome do joão marcelo está com o marcelo cortado', 'Nome corrigido em todos os artefatos (capa do PDF, README, relatório final e diário) e **PDF de prompts refeito** com o anexo dos prompts extraídos das conversas (diário, sessão 42).'],
    ['tente novamente', 'Nova tentativa após a falha de leitura do PDF pelo modelo — a extração foi feita direto do banco de sessões do agente (SQLite), sem depender da leitura do PDF (diário, sessão 42).'],
];

/** Prompts cujo texto é gigante (stack trace colado pelo time) — mantemos o cabeçalho do erro */
function resumir(string $texto): string
{
    if (mb_strlen($texto) <= 1200) {
        return $texto;
    }

    $corte = mb_substr($texto, 0, 1200);
    $corte = preg_replace('/\s+$/', '', $corte);

    return $corte."\n\n*(trecho condensado pelo entregável: o prompt original colou o stack trace completo — 64 frames — do `ParseError`; o cabeçalho do erro, a requisição `GET /portal/aulas/3`, a rota `ChamadaController@aula` e o arquivo `resources/views/portal/aula.blade.php:24` estão íntegros acima)*";
}

function limpar(string $texto): string
{
    $t = preg_replace('/<system-reminder>.*?<\/system-reminder>/s', '', $texto);
    $t = preg_replace('/<environment>.*?<\/environment>/s', '', (string) $t);
    $t = trim((string) $t);
    $t = preg_replace("/\n{3,}/", "\n\n", $t);

    return trim((string) $t);
}

function resultadoPara(string $texto): string
{
    global $resultados;

    $alvo = mb_strtolower($texto);

    foreach ($resultados as [$agulha, $resultado]) {
        if (mb_strpos($alvo, mb_strtolower($agulha)) !== false) {
            return $resultado;
        }
    }

    return '—';
}

$md = [];

$md[] = '# Prompts originais do time — extração do histórico de conversas';
$md[] = '';
$md[] = '> **Anexo de evidência do PDF de prompts (`docs/prompts-ia.pdf`).**';
$md[] = '>';
$md[] = '> Este documento reúne, **na íntegra (verbatim)**, os prompts que o time enviou ao agente de IA durante o hackathon, extraídos do histórico real das conversas (banco de sessões do agente OpenCode, SQLite).';
$md[] = '>';
$md[] = '> - Cada bloco traz o **texto original** (sem edição), o **horário** e o **resultado** obtido.';
$md[] = '> - Os prompts **polidos** (contexto → objetivo → restrições → formato), com numeração `P-01…P-41`, estão no **ATO 2 §6** de `docs/relatorio-final.md`; aqui está a **fonte bruta**, para a banca conferir a completude do processo.';
$md[] = '> - Extração reproduzível: `php scripts/extrair-prompts-conversas.php <opencode.db> docs/prompts-conversas.md` (ver cabeçalho do script).';
$md[] = '';
$md[] = '## Sessões recuperadas';
$md[] = '';
$md[] = '| Sessão | Rótulo | Quando | Natureza |';
$md[] = '|---|---|---|---|';

$total = 0;
$ordem = [];

foreach ($sessoes as $id => [$rotulo, $quando]) {
    $meta = $pdo->query("SELECT title, directory FROM session_v2 WHERE id='$id'")->fetch(PDO::FETCH_ASSOC);
    if (! $meta) {
        continue;
    }

    $n = (int) $pdo->query("SELECT COUNT(*) FROM session_message WHERE session_id='$id' AND type='user'")->fetchColumn();
    $total += $n;

    $primeiro = (int) $pdo->query("SELECT MIN(time_created) FROM session_message WHERE session_id='$id' AND type='user'")->fetchColumn();

    $natureza = str_contains((string) $meta['directory'], 'hackaton')
        ? 'Projeto do hackathon'
        : 'Outro repositório (fora do pacote do hackathon)';

    $md[] = sprintf('| `%s` | %s | %s | %s — **%d prompts** |', substr($id, 0, 12), $rotulo, $quando, $natureza, $n);
    $ordem[$id] = [$rotulo, $quando, $meta['directory'], $natureza, $primeiro];
}

// ordem cronológica: pela primeira mensagem de cada sessão
uasort($ordem, fn ($a, $b) => $a[4] <=> $b[4]);

$md[] = '';
$md[] = "**Total recuperado: {$total} prompts enviados pelo time**, distribuídos em ".count($ordem).' sessões do agente.';
$md[] = '';
$md[] = '---';

$num = 0;

foreach ($ordem as $id => [$rotulo, $quando, $dir, $natureza]) {
    $md[] = '';
    $md[] = "## Sessão {$rotulo}";
    $md[] = '';
    $md[] = "- **Janela:** {$quando}";
    $md[] = "- **Diretório do agente:** `{$dir}`";
    $md[] = "- **Escopo:** {$natureza}";
    $md[] = '';

    $msgs = $pdo->query("SELECT seq, time_created, data FROM session_message WHERE session_id='$id' AND type='user' ORDER BY seq")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($msgs as $m) {
        $d = json_decode($m['data'], true);
        $texto = limpar((string) ($d['text'] ?? ''));

        if ($texto === '') {
            continue;
        }

        $num++;
        $hora = (new DateTimeImmutable('@'.round(((float) $m['time_created']) / 1000)))->setTimezone($tz);
        $data = $hora->format('d/m');
        $hhmm = $hora->format('H:i');

        $md[] = sprintf('### C-%02d · %s %s', $num, $data, $hhmm);
        $md[] = '';
        $md[] = '> **Prompt do time (texto original):**';
        $md[] = '>';

        foreach (explode("\n", resumir($texto)) as $linha) {
            $md[] = '> '.$linha;
        }

        $md[] = '';
        $md[] = '**Resultado:** '.resultadoPara($texto);
        $md[] = '';
    }
}

$md[] = '---';
$md[] = '';
$md[] = '## Como ler este anexo';
$md[] = '';
$md[] = '- **C-xx** = prompt original do time, na ordem cronológica das conversas recuperadas.';
$md[] = '- Prompts de confirmação muito curtos (“blz, escolheremos a 1”, “continue”, “roda aí pra mim testar”) aparecem também — a completude do processo é o quesito avaliado, e eles dão contexto às decisões subsequentes.';
$md[] = '- Duas sessões pertencem a **outro repositório** (curadoria de skills e pendências de autenticação): foram mantidas por transparência, marcadas como fora do pacote do hackathon.';
$md[] = '- A versão **polida** dos mesmos prompts — reescrita no formato contexto → objetivo → restrições → formato de saída, com resultado — está no **ATO 2 §6** do `docs/relatorio-final.md` (`P-01`…`P-41`).';
$md[] = '';
$md[] = '*Documento gerado por extração automática do histórico de conversas do agente (skill `registro-hackathon`, AT04).*';

$conteudo = implode("\n", $md)."\n";

if (! is_dir(dirname($outPath))) {
    mkdir(dirname($outPath), 0777, true);
}

file_put_contents($outPath, $conteudo);

echo "OK — {$num} prompts extraídos → {$outPath} (" . strlen($conteudo) . " bytes)\n";
