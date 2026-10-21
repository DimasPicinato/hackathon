# Presenza — runtime do Assistente de IA (RF29–RF34, RNF07)
# Imagem própria: CLI do agente + a pasta docs embutida como "location" somente leitura.
# A cópia da pasta docs é intencional (mesmo motivo do Dockerfile PHP): o deploy no
# Coolify não suporta bind de fonte confiável (ver journey-log Nº 34).
FROM node:22-bookworm-slim

RUN npm install -g @opencode/cli@2 --no-audit --no-fund

WORKDIR /docs
COPY docs /docs

EXPOSE 4096

# OPENCODE_SERVER_PASSWORD e OPENCODE_API_KEY vêm do compose (variáveis de ambiente).
CMD ["opencode", "serve", "--hostname", "0.0.0.0", "--port", "4096"]
