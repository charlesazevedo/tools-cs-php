# Protocolo do experimento

Experimento de avaliação de ferramentas, planejado segundo Wohlin et al. (2012) e relatado segundo o roteiro de Jedlitschka et al. (2008):
- os **tratamentos** são as ferramentas e as configurações;
- os **objetos** são as variantes de cada CS;
- a **variável dependente** é a detecção de cada variante.

## Objetivo e questões de pesquisa

No formato GQM: *analisar* cinco ferramentas de análise estática para PHP *com o propósito de* avaliar sua cobertura de detecção *com respeito aos* CS relatados em estudos sobre aplicações web PHP, *do ponto de vista de* desenvolvedores e pesquisadores, *no contexto de* uma aplicação web Laravel de código aberto.

- **QP1:** Quais CS cada ferramenta detecta?
- **QP2:** Quanto as ferramentas se complementam?
- **QP3:** Quais CS nenhuma ferramenta detecta, e por quê?

## Code Smells

Os 38 CS são o catálogo de uma revisão sistemática anterior dos autores (https://github.com/charlesazevedo/rls-cs-web-app-php), que reuniu os CS relatados nos estudos sobre aplicações web PHP (`dados/cs-experimento.csv`).

Uma execução preliminar, não publicada, usou 39 CS. Desses, 17 não têm fonte em estudos de PHP e ficaram de fora: 10 não têm registro na revisão, e 7 vêm de um projeto Java. A lista está em `dados/original/cs-removidos.csv`.

## Variantes

Cada CS tem 2 ou 3 variantes (25 CS com duas e 13 com três, 89 no total), descritas em `experimento/exemplos/variantes.csv`, com as convenções em `experimento/exemplos/README.md`:

- **Limiares:** se a definição do CS traz um limiar ("mais de N"), a primeira variante tem N+1 e a segunda, 2N. A exceção é *NPath Complexity*, com 256 e 512, os valores alcançáveis sem ultrapassar o limiar de complexidade ciclomática.
- **Formas:** sem limiar, cada variante é uma forma diferente do mesmo CS.
- **Isolamento:** fora o próprio CS, o código injetado segue o PSR-12, tem comentários de documentação completos, sintaxe compatível com PHP 7.0 e evita outros CS, na medida do possível.
- **Independência das ferramentas:** as variantes foram escritas a partir da definição de cada CS, sem executar as ferramentas sobre elas; só `php -l` foi usado. Elas foram validadas pelo primeiro autor e revisadas pelo segundo.

## Projeto e injeção

Projeto: PDVLaravel (https://github.com/Lucasczm/PDVLaravel), Laravel 5.5, commit `98cddb84521765c29ff7ed691d7f5ba325cc7edc`, sem modificações. Cada execução aplica **uma** variante a uma cópia do projeto original. Há três tipos de injeção:

- **Membros** (52 variantes): o corpo de `class Fragmento { ... }` do `membros.php` da variante é inserido no fim da classe `app/Http/Controllers/Admin/CaixaController.php` (232 linhas, 12 métodos públicos), antes da chave que a fecha. As linhas originais, exceto a chave final, não mudam de número.
- **Arquivos** (36 variantes): classes novas em `app/Experimento/<Cxx>/<Vn>/` e views em `resources/views/experimento/`. Esse tipo atende os CS de classe (tamanho, contagens de membros, complexidade da classe, acoplamento e herança) e os de HTML, CSS e JavaScript. A `CaixaController` já dispara, sem alteração, regras de classe como a de métodos públicos demais.
- **Membros e arquivos** (1 variante): os dois tipos combinados.

O Psalm não analisa os membros de classes nunca referenciadas. Por isso, as variantes com classes novas recebem um arquivo de apoio, `app/Experimento/Referencias.php`, que só as cita; os avisos nesse arquivo não contam.

## Ferramentas e configurações

| Ferramenta | Configuração padrão | Configuração completa |
|---|---|---|
| PHPMD 2.15.0 | os 6 conjuntos de regras embutidos (45 regras) | igual à padrão |
| PHP_CodeSniffer 4.0.4 | PSR12, o padrão de fábrica (60 *sniffs*) | todos os padrões embutidos (204 *sniffs*) |
| PHPStan 2.2.16 | nível 0 | nível `max` (10) |
| Psalm 6.18.0 | `errorLevel` 2 (sem o atributo), `findUnusedCode` | `errorLevel` 1, todas as opções de código não usado, `limitMethodComplexity` |
| SonarQube 26.9 (PHP 3.60, HTML 3.31) | Sonar way (176 regras de PHP, 61 de HTML) | todas as regras ativáveis (245 de PHP, 94 de HTML) |

- **Onde rodam:** PHP 8.3.33 e SonarScanner CLI 8.1.0.6389, em contêineres Docker.
- **O que analisam:** as pastas `app/` e `resources/views/`.
- **Arquivos:** as configurações estão em `experimento/config/`, e as versões e evidências em `experimento/VERSOES.md`.
- **Dependências:** o `vendor/` é instalado do `composer.lock` do projeto sem as exigências de plataforma, porque o Laravel 5.5 pede PHP 7.x, e sem plugins nem scripts do Composer. Ele serve só para as ferramentas resolverem os símbolos.

## Mapeamento regra × CS

Antes da execução, foram mapeadas, para cada CS e cada ferramenta, as regras que o detectam (`dados/regras.csv`). O mapeamento usou só fontes oficiais: a documentação e o código-fonte das versões fixadas. Para cada regra, ele registra a cobertura (total, parcial ou aproximada), se ela está ativa em cada configuração e o limiar de fábrica.

Também antes da execução, ficou registrado, para cada variante, que ferramentas deveriam acusá-la (colunas `esperado_*` de `variantes.csv`).

Depois da execução, o mapeamento recebeu um único acréscimo: `Squiz.Functions.FunctionDeclarationArgumentSpacing.NoSpaceBeforeHint` para *Incorrect Spacing After Commas*. É um código de erro do mesmo *sniff* já mapeado (`NoSpaceBeforeArg`), usado quando o parâmetro seguinte tem tipo declarado.

## Procedimento

1. **Preparação:** imagem Docker com as versões fixadas, download do projeto, instalação das dependências e perfis do SonarQube (`preparar.py`).
2. **Linha de base:** as cinco ferramentas, nas duas configurações, analisam o projeto sem alteração.
3. **Variantes:** cada uma das 89 variantes é aplicada a uma cópia do projeto, e as cinco ferramentas a analisam nas duas configurações. São 90 execuções e 900 análises (`executar.py`).
4. **Análise:** o critério de detecção é aplicado e os resultados são agregados (`analisar.py`).

No SonarQube, cada execução e configuração é um projeto próprio (`cs-exp-<execução>-<configuração>`). A configuração padrão usa os perfis *Sonar way*, e a completa, os perfis "Completo (todas as regras)" de PHP e de HTML.

## Critério de detecção

Uma ferramenta detecta uma variante, numa configuração, quando emite ao menos um aviso que:

1. vem de uma regra mapeada para aquele CS em `dados/regras.csv`;
2. aponta para o que a variante injetou: um arquivo novo, as linhas inseridas na `CaixaController` ou, para avisos de nível de classe, a declaração dela;
3. não existe na linha de base, na mesma regra, arquivo e linha, com as linhas da `CaixaController` corrigidas pelo deslocamento da inserção.

O critério é aplicado por script, sem julgamento manual. Falsos positivos não são medidos. Os avisos de nível *info* do Psalm, que ele oculta por padrão, não contam.

## Análise

A análise é descritiva, sem testes estatísticos. Para cada CS, ferramenta e configuração, o resultado é "todas", "algumas" ou "nenhuma" variante detectada. A partir daí se calculam:
- a cobertura de cada ferramenta;
- a união das ferramentas e a cobertura acumulada, com as ferramentas somadas na ordem que mais acrescenta CS;
- os CS que só uma ferramenta detecta;
- a comparação entre o esperado e o observado.
