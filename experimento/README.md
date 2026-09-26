# Experimento (L6)

Roda as cinco ferramentas sobre o PDVLaravel original (commit `98cddb8`) e
sobre cada uma das 89 variantes de `exemplos/`, nas configurações padrão e
completa, e aplica o critério de detecção da decisão D4.

| arquivo | papel |
|---|---|
| `VERSOES.md` | versões fixadas e evidências (L4) |
| `config/` | configurações padrão e completa de cada ferramenta (L4) |
| `exemplos/` | variantes dos CS (L7) |
| `ferramentas/Dockerfile` | imagem com PHP 8.3 e os PHARs de PHPMD, PHPCS, PHPStan e Psalm, conferidos por SHA-256 |
| `docker-compose.yml` | ferramentas, SonarQube Community Build 26.9 (porta 9010) e SonarScanner CLI 8.1 |
| `preparar.py` | baixa o projeto, instala as dependências travadas, sobe os contêineres, cria os perfis completos do SonarQube e registra as versões (`resultados/ambiente.json`) |
| `executar.py` | monta cada execução, aplica a variante e roda as ferramentas |
| `analisar.py` | aplica o critério de detecção e grava `resultados/` |
| `trabalho/` | fora do git: projeto base, uma pasta por execução, saídas brutas |

## Como rodar

1. Suba o SonarQube e crie um token (uma vez; o token nunca vai para o git):
   ```bash
   docker compose -p cs-experimento up -d --build
   ```
   Abra http://localhost:9010, entre com `admin`/`admin`, troque a senha, gere
   um token de usuário (*My Account → Security*) e grave-o em
   `experimento/.env`, numa linha `SONAR_TOKEN=...`.
2. Prepare o ambiente:
   ```bash
   python3 experimento/preparar.py
   ```
3. Rode e analise:
   ```bash
   python3 experimento/executar.py
   python3 experimento/analisar.py
   ```
   `executar.py --so base C13-v1` roda só algumas execuções, e `--fase locais`
   deixa o SonarQube de fora. O que já rodou não é refeito, salvo com `--refazer`.

## Decisões de execução

- **O que é analisado:** `app/` e `resources/views/` em todas as ferramentas (`comum.FONTES`).
- **PHPStan e Psalm:** recebem uma cópia da configuração na raiz de cada execução, porque resolvem os caminhos em relação ao arquivo de configuração.
- **Dependências:** o `vendor/` é instalado do `composer.lock` do projeto, com `--ignore-platform-reqs`, porque o Laravel 5.5 pede PHP 7.x. Ele serve só para as ferramentas resolverem os símbolos, e as execuções compartilham o da base por um link.
- **Arquivo de apoio do Psalm:** o Psalm não analisa os membros de classes nunca referenciadas. Por isso, as variantes com classes novas recebem `app/Experimento/Referencias.php`, que só as cita (`Classe::class`). Os avisos nesse arquivo não contam.
- **Psalm e avisos de nível *info*:** abaixo do `errorLevel` configurado, o Psalm os oculta por padrão, e aqui eles também não contam.
- **SonarQube, padrão:** cada execução é um projeto novo (`cs-exp-<execução>-<configuração>`), e a configuração padrão usa os perfis *Sonar way*.
- **SonarQube, completa:** o projeto recebe os perfis "Completo (todas as regras)" de PHP e de HTML.
