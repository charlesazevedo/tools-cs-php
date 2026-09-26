# Pacote de replicação: Quais Code Smells as Ferramentas de Análise Estática para PHP Detectam? Um Estudo Experimental

Charles de Azevedo Júnior e Wilkerson de Lucena Andrade — Universidade Federal de Campina Grande (UFCG).

Este pacote reúne o necessário para refazer o experimento do artigo:
- as variantes dos Code Smells (CS);
- o mapeamento entre as regras das ferramentas e os CS;
- as configurações das ferramentas, com as versões fixadas;
- o ambiente Docker e os scripts;
- os resultados.

O protocolo está em [PROTOCOLO.md](PROTOCOLO.md).

No experimento, 89 variantes de 38 CS, os do catálogo de uma revisão sistemática anterior dos autores, são injetadas uma de cada vez no PDVLaravel, uma aplicação Laravel de código aberto. Cada versão é analisada por cinco ferramentas (PHPMD, PHP_CodeSniffer, SonarQube, PHPStan e Psalm), na configuração de fábrica e com todas as regras ativas.

## Conteúdo

| caminho | conteúdo | licença |
|---|---|---|
| `PROTOCOLO.md` | objetivo, questões de pesquisa, objetos, ferramentas, procedimento, critério de detecção e análise | CC BY 4.0 |
| `dados/cs-experimento.csv` | os 38 CS (C01–C38): nome, categoria, especificidade (G genérico, W web, P ligado ao PHP), descrição e estudos de origem | CC BY 4.0 |
| `dados/regras.csv` | mapeamento regra × CS em cada ferramenta: regra, grupo ou nível, cobertura (total, parcial ou aproximada), ativa na configuração padrão e na completa, limiar de fábrica, fonte oficial e observações | CC BY 4.0 |
| `dados/original/` | resultados de uma execução preliminar, não publicada, usados na comparação do artigo (ver o README da pasta) | CC BY 4.0 |
| `experimento/exemplos/` | as 89 variantes (`Cxx-*/vN/`), os metadados (`variantes.csv`: forma, medida, tipo de injeção, o que se esperava de cada ferramenta), as convenções (`README.md`), os geradores das variantes grandes (`gerar/`) e o validador (`validar.py`) | MIT |
| `experimento/config/` | configurações padrão e completa de cada ferramenta | MIT |
| `experimento/ferramentas/`, `experimento/docker-compose.yml` | imagem das ferramentas (PHARs conferidos por SHA-256), SonarQube e SonarScanner | MIT |
| `experimento/preparar.py`, `executar.py`, `analisar.py`, `comum.py` | preparação do ambiente, execução e aplicação do critério de detecção | MIT |
| `experimento/VERSOES.md` | versões fixadas e as evidências das configurações | CC BY 4.0 |
| `experimento/resultados/` | resultados da execução de 25/09/2026 (abaixo) | CC BY 4.0 |
| `saidas-brutas.zip` (anexo da *release*) | saídas brutas das 900 análises | CC BY 4.0 |

As referências a itens como "L4", "L7" ou "D4" nos comentários dos scripts remetem ao plano de revisão do artigo, que não faz parte do pacote. O `PROTOCOLO.md` descreve o que cada um significa.

## Resultados

Em `experimento/resultados/`:

- **`deteccoes-variantes.csv`:** uma linha por variante × ferramenta × configuração (890 linhas). As colunas são:
  - `detectado`: `Sim` ou `Não`, pelo critério do protocolo;
  - `esperado`: o que a documentação das regras previa, registrado antes da execução;
  - `regras_disparadas`: as regras mapeadas que acusaram a variante;
  - `avisos_mapeados`: quantos avisos dessas regras;
  - `avisos_novos_no_local`: todos os avisos novos nos trechos injetados, mapeados ou não.
- **`deteccoes-cs.csv`:** por CS, ferramenta e configuração, quantas variantes foram detectadas e o resultado (`todas`, `algumas` ou `nenhuma`).
- **`avisos-novos.csv`:** todos os avisos novos (ausentes da linha de base) nos trechos injetados, com regra, arquivo, linha e mensagem.
- **`linha-de-base.csv`:** o número de avisos de cada ferramenta e configuração no projeto sem alteração.
- **`ambiente.json`:** as versões efetivamente instaladas, os *digests* das imagens Docker, os perfis do SonarQube e o tamanho da linha de base.
- **`sonarqube/`:** as regras ativas e o *backup* dos perfis "Completo" de PHP e de HTML.

`saidas-brutas.zip` tem, para cada uma das 90 execuções (`base` e `Cxx-vN`):
- `saidas/`: a saída de cada ferramenta e configuração;
- `avisos.csv`: os avisos normalizados;
- `injecao.json`: onde a variante entrou;
- `estado.json`: o código de saída e a duração de cada ferramenta.

## Como reproduzir

Requisitos: Docker com o Compose v2, Python 3.12 (ou 3.8.17+, 3.9.17+, 3.10.12+, 3.11.4+, que têm o `filter` do `tarfile`), cerca de 3 GB de espaço e acesso à internet. O download inclui as imagens (cerca de 2 GB), os PHARs das ferramentas, o PDVLaravel e as dependências dele.

1. Suba o SonarQube e crie um token, uma vez:
   ```bash
   docker compose -f experimento/docker-compose.yml -p cs-experimento up -d --build
   ```
   - Abra http://localhost:9010 e entre com `admin`/`admin`.
   - Troque a senha e gere um token de usuário em *My Account → Security*.
   - Grave o token em `experimento/.env`, numa linha `SONAR_TOKEN=...`; o `experimento/.env.exemple` serve de modelo.
2. Prepare o ambiente: baixa o projeto, instala as dependências, cria os perfis completos e registra as versões.
   ```bash
   python3 experimento/preparar.py
   ```
3. Execute e analise:
   ```bash
   python3 experimento/executar.py
   python3 experimento/analisar.py
   ```
   - A fase das ferramentas locais leva cerca de 25 minutos (quatro execuções em paralelo).
   - A do SonarQube, que processa uma análise por vez, leva de 1 a 2 horas.
   - `executar.py --so base C13-v1` roda só as execuções indicadas, e `--fase locais` deixa o SonarQube de fora.

Para conferir as variantes sem rodar as ferramentas (estrutura, metadados e `php -l`):
```bash
python3 experimento/exemplos/validar.py
```

Com as mesmas versões (fixadas em `experimento/ferramentas/Dockerfile` e em `docker-compose.yml`), as detecções devem ser as mesmas de `experimento/resultados/`.

## O que não está incluído, e por quê

- **O PDVLaravel e as dependências dele.** O projeto não declara licença. Por isso, os scripts o baixam do GitHub no commit `98cddb84521765c29ff7ed691d7f5ba325cc7edc` e instalam as dependências do `composer.lock`. As variantes não contêm código do PDVLaravel.
- **Os artefatos da execução preliminar** (código, planilha, configurações). Eles incluem uma cópia modificada do PDVLaravel. Os resultados dela estão em `dados/original/`.
- **Tokens e a área de trabalho** (`experimento/.env`, `experimento/trabalho/`).

## Apoio de IA generativa

As variantes dos CS, os scripts de execução e de análise, o ambiente Docker e o levantamento das regras de cada ferramenta (o mapeamento regra × CS) foram elaborados com apoio de um assistente de IA generativa (Claude, da Anthropic). Todos esses artefatos foram validados pelos autores:
- as variantes, pelo primeiro autor, com revisão do segundo, e escritas a partir da definição de cada CS, sem executar as ferramentas sobre elas;
- o mapeamento, feito a partir da documentação e do código-fonte oficiais de cada ferramenta, com a fonte de cada regra registrada em `dados/regras.csv`.

## Licenças

- **Código:** as variantes, os scripts, as configurações e o ambiente Docker estão sob a licença MIT ([LICENSE](LICENSE)).
- **Dados e resultados:** `dados/`, `experimento/resultados/`, `experimento/VERSOES.md`, este README, o protocolo e as saídas brutas estão sob a licença CC BY 4.0 ([LICENSE-DADOS](LICENSE-DADOS)).

## Como citar

Artigo: Charles de Azevedo Júnior e Wilkerson de Lucena Andrade. *Quais Code Smells as Ferramentas de Análise Estática para PHP Detectam? Um Estudo Experimental.* Manuscrito, 2026.

Pacote de replicação (este repositório): https://github.com/charlesazevedo/tools-cs-php

Revisão sistemática que originou o catálogo de CS: https://github.com/charlesazevedo/rls-cs-web-app-php
