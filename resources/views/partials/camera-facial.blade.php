{{-- RF03/RF14 — captura facial client-side (face-api.js vendored, sem build).
     A imagem NUNCA sai do navegador: o modelo extrai no cliente um embedding de
     128 dimensões, que é injetado em input hidden `embedding` do form acionado
     (presença RF14.a/RF14.b ou autocadastro /portal/biometria) e enviado com o
     POST nativo (CSRF já embutido nos forms). Referência: overlay da branch
     reconhecimento-facial (8af15f6), aqui com matching REAL no pgvector. --}}
<div id="rec-overlay" class="rec-overlay" hidden>
    <div class="rec-caixa" role="dialog" aria-modal="true" aria-labelledby="rec-titulo">
        <h3 id="rec-titulo">📷 Reconhecimento facial</h3>
        <p class="mini" id="rec-nome"></p>
        <div class="rec-cena">
            <video id="rec-video" autoplay playsinline muted></video>
            <div id="rec-espera" class="rec-espera">Aguardando a câmera…</div>
            <span id="rec-numero" class="rec-numero" hidden></span>
            <span id="rec-flash" class="rec-flash"></span>
        </div>
        <p id="rec-status" class="rec-status"></p>
        <div id="rec-dica" class="rec-dica" hidden>
            <p><strong>A câmera está bloqueada neste navegador. Para liberar:</strong></p>
            <ol>
                <li>Clique no <strong>ícone de cadeado</strong> (ou de ajustes) ao lado do endereço do site.</li>
                <li>Em <strong>Câmera</strong>, escolha <strong>Permitir</strong>.</li>
                <li>Volte aqui e toque em <strong>Já liberei — tentar novamente</strong>.</li>
            </ol>
            <p class="mini">Se continuar bloqueada, confira também nas configurações de privacidade do seu computador se este navegador pode usar a câmera.</p>
        </div>
        <div class="linha-acoes">
            <button type="button" class="btn btn-accent" id="rec-capturar" disabled>Capturar</button>
            <button type="button" class="btn btn-outline" id="rec-repetir" hidden>Tentar novamente</button>
            <button type="button" class="btn btn-outline" id="rec-cancelar">Cancelar</button>
        </div>
        <p class="mini muted">A imagem da câmera é usada apenas neste instante e nunca é armazenada
            nem enviada — o que chega ao servidor é apenas um código numérico que representa o seu
            rosto, comparado ao seu cadastro protegido pelo termo de privacidade que você aceitou.</p>
    </div>
</div>

<script>
(function () {
    'use strict';

    var overlay = document.getElementById('rec-overlay');
    if (!overlay) return;

    var video = document.getElementById('rec-video');
    var espera = document.getElementById('rec-espera');
    var status = document.getElementById('rec-status');
    var numero = document.getElementById('rec-numero');
    var flash = document.getElementById('rec-flash');
    var dica = document.getElementById('rec-dica');
    var botaoCapturar = document.getElementById('rec-capturar');
    var botaoRepetir = document.getElementById('rec-repetir');
    var botaoCancelar = document.getElementById('rec-cancelar');
    var rotulo = document.getElementById('rec-nome');

    var stream = null;
    var formAlvo = null;
    var rostoVisivel = false;
    var loop = null;
    var modelosOk = false;
    var falhaCamera = false;
    var contando = false;
    var autoTimer = null;

    function definirStatus(texto, tipo) {
        status.textContent = texto;
        status.className = 'rec-status' + (tipo ? ' rec-status-' + tipo : '');
    }

    function pararCamera() {
        if (loop) { clearInterval(loop); loop = null; }
        if (autoTimer) { clearTimeout(autoTimer); autoTimer = null; }
        if (stream) {
            stream.getTracks().forEach(function (t) { t.stop(); });
            stream = null;
        }
        // Soltar o stream do elemento evita o quadro "morto" (e a mensagem de mídia inválida)
        video.srcObject = null;
        espera.hidden = false;
        rostoVisivel = false;
        botaoCapturar.disabled = true;
    }

    function fechar() {
        pararCamera();
        contando = false;
        if (autoTimer) { clearTimeout(autoTimer); autoTimer = null; }
        botaoRepetir.hidden = true;
        botaoRepetir.textContent = 'Tentar novamente';
        dica.hidden = true;
        overlay.hidden = true;
        formAlvo = null;
    }

    // Explica, em linguagem de usuário, por que a câmera não abriu e o que fazer agora.
    function tratarErroCamera(erro) {
        pararCamera();
        falhaCamera = true;
        botaoRepetir.hidden = false;
        var nome = erro && erro.name;
        var semPermissao = (nome === 'NotAllowedError' || nome === 'SecurityError');
        var mensagem = 'Não foi possível começar a usar a câmera agora. Tente novamente em instantes — enquanto isso, o instrutor pode registrar a sua presença manualmente.';

        dica.hidden = !semPermissao;
        botaoRepetir.textContent = semPermissao ? 'Já liberei — tentar novamente' : 'Tentar novamente';

        if (semPermissao) {
            mensagem = 'Não conseguimos usar a câmera: o navegador está bloqueando o acesso a ela neste site.';
        } else if (nome === 'NotFoundError' || nome === 'DevicesNotFoundError') {
            mensagem = 'Não encontramos uma câmera neste dispositivo. O instrutor pode registrar a sua presença manualmente.';
        } else if (nome === 'NotReadableError' || nome === 'TrackStartError' || nome === 'AbortError') {
            mensagem = 'A câmera não está nos enviando imagem — ela pode estar em uso por outro programa. Feche o outro programa e toque em Tentar novamente.';
        } else if (nome === 'OverconstrainedError' || nome === 'ConstraintNotSatisfiedError') {
            mensagem = 'A câmera deste dispositivo não conseguiu abrir. Toque em Tentar novamente — se continuar, o instrutor pode registrar a sua presença manualmente.';
        }

        definirStatus(mensagem, 'erro');
    }

    function carregarModelos() {
        if (modelosOk) return Promise.resolve(true);
        var opcoes = { inputSize: 320, scoreThreshold: 0.5 };
        return Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models/faceapi'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models/faceapi'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models/faceapi')
        ]).then(function () {
            modelosOk = true;
            return opcoes;
        });
    }

    function iniciarDeteccao() {
        var detector = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

        loop = setInterval(function () {
            faceapi.detectSingleFace(video, detector).withFaceLandmarks()
                .then(function (resultado) {
                    if (resultado) {
                        if (!rostoVisivel) {
                            rostoVisivel = true;
                            botaoCapturar.disabled = false;
                            definirStatus('Rosto detectado! Capturando automaticamente em instantes… (ou clique em Capturar)');
                            // Início automático assim que o rosto fica disponível na tela;
                            // o botão "Capturar" permanece como alternativa manual.
                            if (!contando && !autoTimer) {
                                autoTimer = setTimeout(function () {
                                    autoTimer = null;
                                    if (rostoVisivel && !contando) {
                                        contando = true;
                                        contagem(function () { contando = false; capturar(); });
                                    }
                                }, 1200);
                            }
                        }
                    } else if (rostoVisivel) {
                        rostoVisivel = false;
                        botaoCapturar.disabled = true;
                        if (autoTimer) { clearTimeout(autoTimer); autoTimer = null; }
                        definirStatus('Nenhum rosto visível — olhe para a câmera.');
                    } else if (status.textContent.indexOf('Nenhum rosto') === -1) {
                        definirStatus('Nenhum rosto visível — olhe para a câmera.');
                    }
                })
                .catch(function () { /* frames corrompidos no meio do boot da câmera */ });
        }, 350);
    }

    function contagem(fn) {
        var restante = 3;
        numero.hidden = false;
        numero.textContent = restante;
        var t = setInterval(function () {
            restante--;
            if (restante > 0) {
                numero.textContent = restante;
                return;
            }
            clearInterval(t);
            numero.hidden = true;
            flash.classList.add('rec-flash-ativo');
            setTimeout(function () { flash.classList.remove('rec-flash-ativo'); }, 220);
            fn();
        }, 700);
    }

    function capturar() {
        contando = false;
        if (!stream || video.paused || video.videoWidth === 0) {
            definirStatus('A câmera não está pronta para a captura. Toque em Tentar novamente.', 'erro');
            botaoRepetir.hidden = false;
            return;
        }
        botaoCapturar.disabled = true;
        definirStatus('Analisando o rosto…');
        clearInterval(loop);
        loop = null;

        var detector = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });

        faceapi.detectSingleFace(video, detector).withFaceLandmarks().withFaceDescriptor()
            .then(function (resultado) {
                if (!resultado || !resultado.descriptor) {
                    pararCamera();
                    definirStatus('Não conseguimos ler o seu rosto no momento da captura. Ajuste a iluminação, fique bem próximo da câmera e tente de novo.', 'erro');
                    return;
                }
                var embedding = Array.from(resultado.descriptor);
                if (formAlvo && formAlvo.dataset && formAlvo.dataset.modo === 'teste') {
                    definirStatus('Comparando com o seu cadastro…');
                    var campoToken = document.querySelector('meta[name="csrf-token"]');
                    var fd = new FormData();
                    if (campoToken) fd.append('_token', campoToken.content);
                    fd.append('embedding', JSON.stringify(embedding));
                    fetch(formAlvo.action, {
                        method: 'POST',
                        body: fd,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    }).then(function (r) { return r.json(); }).then(function (data) {
                        pararCamera();
                        overlay.hidden = true;
                        var area = document.getElementById('teste-facial-resultado');
                        if (area) {
                            area.hidden = false;
                            area.className = 'alert ' + (data.reconhecido ? 'alert-ok' : 'alert-erro');
                            var pct = (typeof data.similaridade === 'number') ? ' (similaridade ' + Math.round(data.similaridade * 100) + '%)' : '';
                            area.textContent = (data.mensagem || 'Resultado do teste.') + pct;
                        }
                        formAlvo = null;
                    }).catch(function () {
                        pararCamera();
                        overlay.hidden = true;
                        var area = document.getElementById('teste-facial-resultado');
                        if (area) { area.hidden = false; area.className = 'alert alert-erro'; area.textContent = 'Não foi possível concluir o teste agora. Tente novamente.'; }
                        formAlvo = null;
                    });
                    return;
                }
                formAlvo.querySelector('input[name="embedding"]').value = JSON.stringify(embedding);
                overlay.hidden = true;
                pararCamera();
                formAlvo.submit(); // POST nativo: embedding + CSRF do próprio form
            })
            .catch(function () {
                pararCamera();
                definirStatus('Algo deu errado ao analisar a captura. Tente novamente.', 'erro');
            });
    }

    function abrir(form) {
        formAlvo = form;
        rotulo.textContent = form.dataset.nome ? 'Colaborador: ' + form.dataset.nome : 'Modo: ' + (form.dataset.modo === 'cadastro' ? 'cadastro do rosto' : 'validação de presença');
        overlay.hidden = false;
        botaoRepetir.hidden = true;
        botaoRepetir.textContent = 'Tentar novamente';
        dica.hidden = true;
        espera.hidden = false;
        falhaCamera = false;

        if (window.isSecureContext === false) {
            definirStatus('A câmera só funciona em uma conexão segura (endereço começando com https). Peça ao instrutor para registrar a sua presença manualmente.', 'erro');
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            definirStatus('Seu navegador não permite o uso da câmera aqui. Peça ao instrutor para registrar a sua presença manualmente.', 'erro');
            return;
        }

        // Pede a câmera AGORA, ainda dentro do clique do usuário: é isso que faz o
        // navegador exibir o pedido de permissão (Safari/iOS só mostra com gesto).
        // Os modelos de reconhecimento carregam em paralelo, sem segurar o pedido.
        definirStatus('Aguardando a sua autorização — quando o navegador perguntar, escolha Permitir para usar a câmera.');

        var pedidoCamera = navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } })
            .then(function (s) {
                stream = s;
                video.srcObject = s;
                if (!modelosOk) definirStatus('Câmera liberada — preparando o reconhecimento facial…');
                return video.play().catch(function () { /* alguns navegadores só tocam após um toque */ });
            })
            .then(function () {
                // Vigia: confirma que a câmera realmente entrega imagem (não só "abriu").
                var tentativas = 0;
                var vigia = setInterval(function () {
                    tentativas++;
                    if (!stream) { clearInterval(vigia); return; }
                    if (video.videoWidth > 0 && !video.paused && video.readyState >= 2) {
                        espera.hidden = true;
                        clearInterval(vigia);
                        return;
                    }
                    if (tentativas >= 5) {
                        clearInterval(vigia);
                        tratarErroCamera({ name: 'NotReadableError' });
                    }
                }, 1000);
            });

        var pedidoModelos = carregarModelos();

        pedidoCamera.catch(function (erro) { tratarErroCamera(erro); });
        pedidoModelos.catch(function () {
            pararCamera();
            if (falhaCamera) return; // a mensagem da câmera é a causa principal
            botaoRepetir.hidden = false;
            definirStatus('Não foi possível preparar o reconhecimento facial agora. Toque em Tentar novamente em instantes — se continuar, o instrutor pode registrar a sua presença manualmente.', 'erro');
        });

        Promise.all([pedidoCamera, pedidoModelos])
            .then(function () {
                if (falhaCamera) return; // câmera caiu (vigia/erro) depois de liberar
                botaoRepetir.hidden = true;
                definirStatus('Olhe para a câmera — aguardando detectar um rosto.');
                iniciarDeteccao();
            })
            .catch(function () { /* mensagens já exibidas pelos tratamentos acima */ });
    }

    // face-api.min.js entra com `defer`: só estará pronto antes de DOMContentLoaded.
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.faceapi === 'undefined') return; // ambiente sem o vendor: forms seguem nativos

        document.querySelectorAll('form[data-facial]').forEach(function (form) {
            // input hidden que recebe o vetor antes do submit nativo
            if (!form.querySelector('input[name="embedding"]')) {
                var campo = document.createElement('input');
                campo.type = 'hidden';
                campo.name = 'embedding';
                form.appendChild(campo);
            }

            form.addEventListener('submit', function (evento) {
                if (form.querySelector('input[name="embedding"]').value) return; // segundo submit (após captura)
                evento.preventDefault();
                abrir(form);
            });
        });

        botaoCapturar.addEventListener('click', function () { if (contando) return; if (autoTimer) { clearTimeout(autoTimer); autoTimer = null; } contando = true; contagem(function () { contando = false; capturar(); }); });
        botaoRepetir.addEventListener('click', function () { if (formAlvo) abrir(formAlvo); });
        botaoCancelar.addEventListener('click', fechar);
        video.addEventListener('playing', function () { espera.hidden = true; });
        video.addEventListener('loadeddata', function () { espera.hidden = true; });
        video.addEventListener('error', function () {
            if (!overlay.hidden) tratarErroCamera({ name: 'NotReadableError' });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !overlay.hidden) fechar();
        });
    });
})();
</script>
