const PainelLogin = {

    senha: null,

    entrar() {
        this.senha = document.getElementById('senha').value;

        if (!this.validar()) {
            alert("Acesso negado");
            return;
        }

        document.getElementById('login').classList.add('hidden');
        document.getElementById('painel').classList.remove('hidden');

        PainelChamadas.init();
    },

    validar() {
        return this.senha === "1234"; // depois vira Laravel Auth
    }
};


const PainelChamadas = {

    ultimoId: null,
    historico: [],

    init() {
        this.carregar();
        setInterval(() => this.carregar(), 4000);
    },

    async carregar() {

        const res = await fetch('/api/painel');
        if (!res.ok) return;

        const dados = await res.json();
        if (!dados.length) return;

        const atual = dados[0];

        if (this.ultimoId !== atual.id) {
            this.ultimoId = atual.id;

            SomService.tocar();
            VozService.falar(atual.nome);

            this.addHistorico(atual);
        }

        this.renderAtual(atual);
        this.renderHistorico();
    },

    renderAtual(atual) {
        document.getElementById('atual').innerText = atual.id ?? '---';
        document.getElementById('nomeAtual').innerText = atual.nome ?? '---';
        document.getElementById('localAtual').innerText = atual.local ?? '---';
    },

    addHistorico(item) {
        this.historico.unshift(item);
        if (this.historico.length > 3) this.historico.pop();
    },

    renderHistorico() {

        const el = document.getElementById('ultimos');

        el.innerHTML = this.historico.map(i => `
            <div class="bg-gray-800 p-4 rounded text-center">
                <div class="text-xl font-bold">${i.id}</div>
                <div>${i.nome}</div>
            </div>
        `).join('');
    }
};


const SomService = {
    tocar() {
        const audio = new Audio('/sons/chamada.mp3');

        let i = 0;
        const play = () => {
            if (i++ >= 3) return;
            audio.currentTime = 0;
            audio.play();
            setTimeout(play, 1200);
        };

        play();
    }
};


const VozService = {
    falar(texto) {

        if (!('speechSynthesis' in window)) return;

        let i = 0;

        const falar = () => {
            if (i++ >= 3) return;

            const msg = new SpeechSynthesisUtterance(texto);
            msg.lang = "pt-BR";
            speechSynthesis.speak(msg);

            setTimeout(falar, 1500);
        };

        falar();
    }
};