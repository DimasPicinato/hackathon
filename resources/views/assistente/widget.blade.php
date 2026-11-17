{{-- RF29–RF34 — widget público do Assistente Presenza (sem login; aviso LGPD; histórico só na aba) --}}
<link rel="stylesheet" href="/css/assistente.css">
<div class="assistente" x-data="assistenteWidget()" x-cloak>
    <button type="button" class="assistente-botao" @click="abrir = !abrir"
            :aria-expanded="abrir ? 'true' : 'false'" aria-controls="assistente-painel"
            :aria-label="abrir ? 'Fechar assistente' : 'Abrir assistente'">P</button>

    <section id="assistente-painel" class="assistente-painel" x-show="abrir" x-cloak
             role="dialog" aria-label="Assistente Presenza" @keydown.escape.window="abrir = false">
        <header class="assistente-cabecalho">
            <strong>Assistente Presenza</strong>
            <button type="button" class="assistente-fechar" @click="abrir = false" aria-label="Fechar assistente">&times;</button>
        </header>

        <p class="assistente-aviso">
            &#9888; Esta conversa fica registrada com data e horário para segurança do serviço. Não envie dados pessoais.
        </p>

        <div class="assistente-mensagens" x-ref="lista" aria-live="polite">
            <template x-for="msg in mensagens" :key="msg.id">
                <div class="assistente-msg" :class="msg.autor === 'voce' ? 'assistente-msg-voce' : 'assistente-msg-assistente'" x-text="msg.texto"></div>
            </template>
            <div class="assistente-digitando" x-show="carregando">Assistente escrevendo&hellip;</div>
        </div>

        <form class="assistente-form" @submit.prevent="enviar()">
            <textarea x-model="pergunta" maxlength="500" rows="1" placeholder="Digite sua pergunta..." required></textarea>
            <button type="submit" class="assistente-enviar" :disabled="carregando || pergunta.trim().length < 3" aria-label="Enviar pergunta">&rarr;</button>
        </form>

        <p class="assistente-rodape">IA · responde só sobre o Presenza · sem dados pessoais</p>
    </section>
</div>
@once
    <script src="/js/alpine.min.js" defer></script>
    <script>
        function assistenteWidget() {
            return {
                abrir: false,
                carregando: false,
                pergunta: '',
                seq: 1,
                sessao: (window.crypto && crypto.randomUUID ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2)),
                mensagens: [{
                    id: 1,
                    autor: 'assistente',
                    texto: 'Olá! Sou o Assistente Presenza. Explico como funciona a gestão de treinamentos: vagas, presença, certificados e verificação. Nenhuma informação sua é necessária para conversar.'
                }],

                async enviar() {
                    if (this.carregando) return;
                    const texto = this.pergunta.trim();
                    if (texto.length < 3) return;

                    this.mensagens.push({ id: this.seq++, autor: 'voce', texto: texto });
                    this.pergunta = '';
                    this.carregando = true;
                    this.rolar();

                    try {
                        // O servidor pode levar até ~100 s (modelo free com cold start);
                        // aborta um pouco além disso para o painel nunca ficar preso.
                        const resposta = await fetch('/assistente', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ sessao: this.sessao, pergunta: texto }),
                            signal: AbortSignal.timeout(120000)
                        });
                        let dados = null;
                        try { dados = await resposta.json(); } catch (e) { dados = null; }

                        if (resposta.ok && dados && dados.estado === 'ok') {
                            this.mensagens.push({ id: this.seq++, autor: 'assistente', texto: dados.resposta });
                        } else if (resposta.status === 422) {
                            this.mensagens.push({ id: this.seq++, autor: 'assistente',
                                texto: 'A pergunta precisa ter entre 3 e 500 caracteres. Tente reformular.' });
                        } else {
                            // fallback/erro do servidor: a cortesia do servidor quando existir
                            this.mensagens.push({ id: this.seq++, autor: 'assistente',
                                texto: (dados && dados.resposta) || 'O assistente está indisponível neste momento. Fale com o RH da Usina ou abra um ticket pelo portal.' });
                        }
                    } catch (erro) {
                        this.mensagens.push({
                            id: this.seq++,
                            autor: 'assistente',
                            texto: 'O assistente está indisponível neste momento. Fale com o RH da Usina ou abra um ticket pelo portal.'
                        });
                    } finally {
                        this.carregando = false;
                        this.rolar();
                    }
                },

                rolar() {
                    this.$nextTick(() => {
                        const lista = this.$refs.lista;
                        if (lista) lista.scrollTop = lista.scrollHeight;
                    });
                }
            };
        }
    </script>
@endonce
