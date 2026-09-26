# Versões das ferramentas e evidências das configurações (L4)

Levantado em 25/09/2026 na documentação e no código-fonte oficiais de cada ferramenta (item L4 de `revisao/PLANO-DE-REVISAO.md`). As configurações estão em `config/`; o mapeamento regra × CS, em `../dados/regras.csv`.

---

## Versões e configurações: PHPMD e PHP_CodeSniffer

Levantamento feito em 2026-09-25, com a API de releases do GitHub e o código-fonte das tags abaixo (baixado em `src/`). As duas ferramentas também foram executadas (PHP 8.3.6) sobre os arquivos de amostra em `teste/` para confirmar mensagens, códigos de erro e limiares.

### Versões

| Ferramenta | Última release estável | Data | URL |
|---|---|---|---|
| PHPMD | `2.15.0` | 2023-12-11 | https://github.com/phpmd/phpmd/releases/tag/2.15.0 |
| PHP_CodeSniffer | `4.0.4` | 2026-08-06 | https://github.com/PHPCSStandards/PHP_CodeSniffer/releases/tag/4.0.4 |

- PHPMD: `releases/latest` e a lista de tags indicam 2.15.0 como a última. O branch `master` recebe commits (o último é de 2026-08-02), mas nada foi lançado depois de 2.15.0. Uma eventual versão 3.x não foi avaliada.
- PHPCS: segundo as notas da release, 4.0.2, 4.0.3 e 4.0.4 são idênticas, a não ser pelo número de versão; só os assets PHAR mudaram. A série 3.x continua recebendo manutenção (última: `3.13.6`, 2026-08-06).
- O PHPCPD (`sebastianbergmann/phpcpd`), que era a ferramenta de detecção de clones do ecossistema, está **arquivado** no GitHub (último push em 2023-01-10).

### PHPMD: padrão = completa (arquivos idênticos na prática)

- O PHPMD não tem um ruleset implícito. O terceiro argumento posicional (ruleset) é obrigatório: `CommandLineOptions.php` lança a mensagem de uso quando há menos de 3 argumentos (`if (count($arguments) < 3)`), e o texto de uso diz "Mandatory arguments: ... 3) A ruleset filename or a comma-separated string of ruleset filenames". Isso foi confirmado ao rodar `phpmd <dir> text`.
  https://github.com/phpmd/phpmd/blob/2.15.0/src/main/php/PHPMD/TextUI/CommandLineOptions.php
- O ruleset de exemplo documentado no `README.rst` da tag referencia exatamente os seis rulesets embutidos (`codesize`, `cleancode`, `controversial`, `design`, `naming`, `unusedcode`). A documentação (https://phpmd.org/documentation/index.html) mostra invocações com um único ruleset (`codesize`) ou com vários separados por vírgula.
  https://github.com/phpmd/phpmd/blob/2.15.0/README.rst
- A 2.15.0 traz **só esses seis rulesets**, com 45 regras no total (cleancode 8, codesize 10, controversial 6, design 9, naming 8, unusedcode 4; contagem corrigida em 26/09/2026 lendo o XML: as regras NCSS do codesize e parte do naming estão comentadas). Veja https://github.com/phpmd/phpmd/tree/2.15.0/src/main/resources/rulesets e https://phpmd.org/rules/index.html. Não existe um atalho `all`.
- Por isso, a configuração **padrão** (o ruleset do README, que é também a invocação do experimento original, `cleancode,codesize,controversial,design,naming,unusedcode`) é **idêntica** à **completa**. Os arquivos `phpmd-padrao.xml` e `phpmd-completa.xml` diferem apenas no nome e no comentário.
- Semântica dos limiares, conferida no código-fonte e em execução: as regras cujo parâmetro se chama `minimum`/`maximum`/`reportLevel` disparam quando **valor ≥ limiar**. São elas: ExcessiveClassLength, ExcessiveMethodLength, ExcessiveParameterList, ExcessivePublicCount, CyclomaticComplexity, NPathComplexity, ExcessiveClassComplexity, CouplingBetweenObjects, DepthOfInheritance e NumberOfChildren. Já as regras com `maxfields`/`maxmethods` disparam quando **valor > limiar**: TooManyFields, TooManyMethods e TooManyPublicMethods. Por exemplo, ExcessiveParameterList já dispara com 10 parâmetros, e DepthOfInheritance com 6 ancestrais.

### PHP_CodeSniffer: padrão ≠ completa

#### Padrão (`phpcs-padrao.xml`): PSR12, não PEAR

- No PHPCS **4.x**, o padrão usado quando não se passa `--standard` é o **PSR12**. `Config::restoreDefaults()` define `$this->standards = ['PSR12'];`, e o `CHANGELOG-4.x.md` (4.0.0, seção *Changed*) registra: "The default coding standard has changed from `PEAR` to `PSR12`".
  https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/4.0.4/src/Config.php
  https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/4.0.4/CHANGELOG-4.x.md
- Há duas exceções que também valem como "padrão". Se existir `.phpcs.xml`, `phpcs.xml`, `.phpcs.xml.dist` ou `phpcs.xml.dist` no diretório corrente ou acima dele, esse arquivo é usado (`Config::CONFIG_FILENAMES`). A opção de configuração `default_standard` também substitui o PSR12. Nesta máquina, `phpcs --config-show` está vazio.
- No PHPCS **3.x**, o padrão implícito era **PEAR** (`3.13.6/src/Config.php`, linha 530: `$this->standards = ['PEAR'];`). A crença "o padrão é PEAR" vale, portanto, só para o 3.x. Se o experimento original rodou um PHPCS 3.x sem `--standard`, ele usou o PEAR. Por isso a coluna `observacao` do CSV informa, para cada sniff, se ele está no PEAR.
- Composição, segundo `phpcs --standard=PSR12 -e`: 60 sniffs (Generic 15, PEAR 1, PSR1 3, PSR2 9, PSR12 17, Squiz 15). Para comparação, o PEAR tem 28 sniffs (Generic 9, PEAR 18, Squiz 1).

#### Completa (`phpcs-completa.xml`)

- Reúne os **204 sniffs** existentes na 4.0.4: Generic 79, PEAR 18, PSR1 3, PSR2 12, PSR12 17, Squiz 73, Zend 2. Cada sniff é referenciado individualmente e roda com as propriedades padrão da própria classe. A contagem foi validada com `phpcs --standard=phpcs-completa.xml -e`, que informa "contains 204 sniffs".
- Por que não usar `<rule ref="PEAR"/>`, `<rule ref="Squiz"/>` etc.: os rulesets dos padrões mudam propriedades (por exemplo, `Generic.Files.LineLength`: 85 no PEAR, 120 no PSR2/PSR12/Squiz, 80/120 no Zend) e desligam códigos de erro com `<severity>0</severity>` (o PSR2 e o PSR12 desligam vários, e o Squiz desliga `Generic.CodeAnalysis.EmptyStatement.DetectedCATCH`). Somar os padrões herdaria essas alterações, e a ordem de inclusão decidiria o resultado final.
- O padrão **MySource** foi removido no 4.0.0 (estava *deprecated* desde o 3.x). O 4.0.0 também removeu toda a categoria `Squiz.CSS`, os sniffs `Generic.Debug.JSHint/ESLint/CSSLint/ClosureLinter`, `Squiz.Debug.JSLint/JavaScriptLint` e o suporte a escanear arquivos JS/CSS. **Não há mais nada para JS/CSS no PHPCS 4**, e isso afeta a comparação com o estudo de Soltanifar (2016), que usou PHPCS com JSHint.
- A configuração completa contém sniffs contraditórios entre si: OpeningFunctionBraceBsdAllman × KernighanRitchie, Generic.Classes.OpeningBraceSameLine × PEAR.Classes.ClassDeclaration, LowerCaseConstant × UpperCaseConstant, DisallowTabIndent × DisallowSpaceIndent e DisallowLongArraySyntax × DisallowShortArraySyntax. Qualquer código vai gerar violações de um dos lados de cada par.

### Observações pontuais

- **Duplicate Code (C24)**: nenhuma das duas ferramentas detecta clones. O PHPMD 2.15.0 só tem `DuplicatedArrayKey` (chave repetida em array literal). Clones eram tarefa do PHPCPD, que hoje está arquivado.
- **Laços/incrementadores**: o PHPMD tem apenas `CountInLoopExpression` (design), que acusa `count()`/`sizeof()` na condição de laços. O PHPCS tem `Generic.CodeAnalysis.JumbledIncrementer` ("Loop incrementer ($i) jumbling with inner loop"), `Generic.CodeAnalysis.ForLoopWithTestFunctionCall`, `Generic.CodeAnalysis.ForLoopShouldBeWhileLoop` e `Squiz.PHP.DisallowSizeFunctionsInLoops`. Nenhum deles corresponde a um dos 38 CS. Todos ficam fora do PSR12 e do PEAR, e os três primeiros existem só no padrão Generic.
- **Empty catch no Squiz**: o ruleset Squiz tenta silenciar `Generic.CodeAnalysis.EmptyStatement.DetectedCATCH`, mas no 4.0.4 o código emitido é `DetectedCatch` (`'Detected' . ucfirst(strtolower($name))`). Na execução com `--standard=Squiz`, o erro foi reportado.
- **Mensagens do estudo original (PHPCS)**, todas reproduzidas em execução:
  - C32, "Tag %s cannot be grouped with parameter tags in a doc comment": `Generic.Commenting.DocComment.NonParamGroup`.
  - C34, "Missing parameter name": `PEAR.Commenting.FunctionComment.MissingParamName` e `Squiz.Commenting.FunctionComment.MissingParamName`.
  - C35, "Opening brace should be on a new line": a mensagem é definida em `Generic.Functions.OpeningFunctionBraceBsdAllman`. Ela é emitida como `...BsdAllman.BraceOnSameLine` (Generic, Zend), como `PEAR.Functions.FunctionDeclaration.BraceOnSameLine` (PEAR, que delega ao BsdAllman) e como `Squiz.Functions.MultiLineFunctionDeclaration.BraceOnSameLine` (Squiz, PSR2, PSR12).
  - C38, "There must be exactly one blank line before the tags in a doc comment": `Generic.Commenting.DocComment.SpacingBeforeTags`.
  - Os sniffs de C32, C34 e C38 estão no PEAR e no Squiz, mas **não** no PSR12. Isso é coerente com o estudo ter usado o PEAR como padrão implícito.

### Arquivos

- `regras-phpmd-phpcs.csv`: 100 linhas, uma por (CS, ferramenta, regra), cobrindo os 38 CS × 2 ferramentas. É gerado por `gerar_csv.py`, que lê `ex/matrix.json`, a matriz sniff → padrões extraída de `phpcs --standard=X -e`.
- `phpmd-padrao.xml` e `phpmd-completa.xml` (idênticos na prática); `phpcs-padrao.xml` (PSR12) e `phpcs-completa.xml` (204 sniffs).
- `teste/Amostra*.php`: arquivos de amostra usados nas execuções de verificação.

---

## SonarQube: versões, configurações e duplicação (levantado em 2026-09-25)

Fontes: API do GitHub (`api.github.com/repos/SonarSource/...`), raw.githubusercontent.com, API do Docker Hub. Nada tirado de memória: cada item abaixo tem URL.

### 1. Versões

| Componente | Versão | Data | Fonte |
|---|---|---|---|
| Servidor: **SonarQube Community Build** (imagem Docker gratuita) | `sonarqube:26.9.0.129388-community` (tags `community` e `latest` têm o mesmo digest `sha256:58b068af30bd…`) | imagem atualizada em 2026-09-16; release no GitHub em 2026-09-02 | https://hub.docker.com/v2/repositories/library/sonarqube/tags?page_size=60 ; https://github.com/SonarSource/sonarqube/releases/tag/26.9.0.129388 |
| **sonar-php empacotado** no Community Build 26.9 | **3.60.0.16641** | 2026-08-03 | `build.gradle` do servidor, linha `dependency 'org.sonarsource.php:sonar-php-plugin:3.60.0.16641'`: https://github.com/SonarSource/sonarqube/blob/26.9.0.129388/build.gradle ; lista de plugins empacotados: https://github.com/SonarSource/sonarqube/blob/26.9.0.129388/sonar-application/bundled_plugins.gradle |
| sonar-php, último release | 4.1.0.16998 | 2026-09-24 | https://github.com/SonarSource/sonar-php/releases/tag/4.1.0.16998 (o `master` do SonarQube já usa 4.1.0.16998: https://github.com/SonarSource/sonarqube/blob/master/build.gradle ; deve chegar no próximo Community Build) |
| sonar-html empacotado no 26.9 (analisa também `.php`, ver §4) | 3.31.0.8334 | 2026-08-18 | mesmo `build.gradle`; https://github.com/SonarSource/sonar-html/releases/tag/3.31.0.8334 |
| **SonarScanner CLI**, último release | **8.1.0.6389** | 2026-04-21 | https://github.com/SonarSource/sonar-scanner-cli/releases/tag/8.1.0.6389 |
| Imagem Docker do scanner | `sonarsource/sonar-scanner-cli:12.2.0.4256_8.1.0` (imagem 12.2 com o CLI 8.1.0) | 2026-09-17 | https://hub.docker.com/v2/repositories/sonarsource/sonar-scanner-cli/tags?page_size=15 |

Edições pagas (Developer/Enterprise, tags `2026.4.1-developer` etc.) não foram consideradas.

**sonar-php 3.60 vs 4.1.** Comparei os 245 `.json` de metadados e o `Sonar_way_profile.json` nas duas tags: título, tipo, status e severidade são **idênticos**, e o Sonar way tem as mesmas 176 regras. Entre as classes de regra, só 11 arquivos mudam. Os relevantes aqui: `UnusedFunctionParametersCheck` (S1172; a documentação da 4.x passa a citar a exceção para parâmetros `$_`) e `DuplicatedMethodCheck` (S4144; muda a exceção para acessores). Nenhum limiar padrão mudou. O mapeamento usa as URLs da tag **3.60.0.16641**, a versão que o servidor realmente executa.

**Para fixar a versão no experimento:** use `sonarqube:26.9.0.129388-community` e `sonarsource/sonar-scanner-cli:12.2.0.4256_8.1.0` (ou o CLI 8.1.0.6389 em zip), em vez de `latest`. O experimento original usou `sonarqube:latest` e o scanner 5.0.1.3006. Hoje `latest` aponta para o Community Build, então a versão do servidor usada na época não pode ser recuperada a partir da tag.

### 2. Configurações

#### padrão: perfil embutido "Sonar way" (PHP)

- Fonte: https://github.com/SonarSource/sonar-php/blob/3.60.0.16641/php-checks/src/main/resources/org/sonar/l10n/php/rules/php/Sonar_way_profile.json. São **176 regras** das 245 do analisador PHP (232 `ready` e 13 `deprecated`). O Sonar way não inclui nenhuma deprecated.
- Parâmetros: os valores padrão de cada `@RuleProperty` nas classes `php-checks/src/main/java/org/sonar/php/checks/*.java` (os limiares estão no CSV).
- Histórico: o Sonar way tem a mesma composição nas regras relevantes desde pelo menos a 3.30.0.9766. Conferi S107, S1172, S1448, S138, S108, S110 e S3776, que estão nele, e S1311, S1820, S2042, S1200 e S1541, que não estão.

#### completa: todas as regras PHP ativas (perfil personalizado)

- O perfil embutido não pode ser editado (`checkNotBuiltIn` em `ActivateRulesAction`). Por isso o script cria um perfil novo e faz uma ativação em massa com `api/qualityprofiles/activate_rules`, usando `targetKey=<chave>`, `languages=php` e `statuses=READY,BETA,DEPRECATED`. Depois torna o perfil padrão (`set_default`) ou o associa a um projeto (`add_project`).
- Código conferido na tag 26.9.0.129388: `ActivateRulesAction.java`, `CreateAction.java`, `SetDefaultAction.java`, `AddProjectAction.java` e `RuleWsSupport.defineGenericRuleSearchParameters` (aceita `languages` e `statuses`), em https://github.com/SonarSource/sonarqube/tree/26.9.0.129388/server/sonar-webserver-webapi/src/main/java/org/sonar/server/qualityprofile/ws.
- Script: `sonar-perfil-completo.sh`. O token vem de `SONAR_TOKEN` e nunca é gravado no script. O script confere, com `api/rules/search?activation=false`, que nenhuma regra ficou inativa. Também grava a lista das regras ativas e um backup XML do perfil, que pode ir para o pacote de replicação.
- Regras deprecated: incluídas por padrão (`INCLUDE_DEPRECATED=1`), porque uma delas é a php:S1311 (complexidade ciclomática de classe, C20). Continuam ativáveis, mas o texto da regra diz que ela "will eventually be removed".
- Regras-modelo e regras que exigem parâmetro: o analisador PHP **não tem regras-modelo** (nenhuma classe usa `@RuleTemplate`, nenhum `.json` tem `template`). No PHP, a única regra com parâmetro "vazio" é a php:S1451 (cabeçalho de licença, `headerFormat=""`), e ela **não é inerte**. Com o valor vazio, `FileHeaderCheck` exige que a linha seguinte a `<?php` seja vazia e gera um issue por arquivo quando não é. Isso é ruído na configuração completa, embora não afete os 38 CS. No analisador HTML, `IllegalElementCheck` e `IllegalAttributeCheck` têm padrão vazio: ativas, mas sem efeito sem configuração.
- Regras que se contradizem: com tudo ativo, php:S1105 (`{` no fim da linha) e php:S1106 (`{` no início da linha) disparam juntas, e ambas se sobrepõem a php:S1808 (formatação PER). Qualquer chave de abertura gera ao menos um issue na configuração completa. Isso deve ser dito no artigo ao comparar ferramentas.

### 3. Duplicação de código (C24)

- **No servidor atual, duplicação é só métrica, não issue.** A regra `common-php:DuplicatedBlocks` (repositório "common-<linguagem>") foi removida no **SonarQube 10.0**, no commit "SONAR-16198 removing the 6 built in common rules" (2023-02-24): https://github.com/SonarSource/sonarqube/commit/576b4a83fba9f52406b602035bcc137fc3f69eaa.
  - A classe `DuplicatedBlockRule.java` existe nas tags 7.9, 8.9.0.43852 e 9.9.0.65466 e não existe (HTTP 404) em 10.0.0.68432, 10.8.0.100206 e 26.9.0.129388. Caminho: `server/sonar-ce-task-projectanalysis/src/main/java/org/sonar/ce/task/projectanalysis/issue/commonrule/DuplicatedBlockRule.java`.
  - Em 26.9 só sobra a classe de constantes `server/sonar-server-common/src/main/java/org/sonar/server/rule/CommonRuleKeys.java`, sem regra.
- O que existe: a detecção de cópias (CPD) da plataforma, alimentada pelos tokens que o sonar-php emite (`php-frontend/src/main/java/org/sonar/php/metrics/CpdVisitor.java`, https://github.com/SonarSource/sonar-php/blob/3.60.0.16641/php-frontend/src/main/java/org/sonar/php/metrics/CpdVisitor.java). Ela gera as métricas `duplicated_blocks`, `duplicated_lines`, `duplicated_lines_density` e `duplicated_files`, visíveis na aba Duplications e usáveis em Quality Gate, mas **não gera issues**.
  - Limiar documentado para linguagens que não são Java: pelo menos 100 tokens duplicados em sequência, espalhados por pelo menos 10 linhas (https://docs.sonarsource.com/sonarqube-community-build/user-guide/code-metrics/metrics-definition). Esse limiar vem da documentação; não o encontrei no código-fonte.
  - O `CpdVisitor` do PHP **ignora HTML inline** (`visitInlineHTML` → skip) e **normaliza literais** (strings viram `$CHARS`, números viram `$NUMBER`). Logo, o JavaScript duplicado dentro de HTML ou de strings (C09) não é comparado.
- Consequência para o experimento: para C24, o SonarQube "detecta" apenas como métrica. Se o protocolo conta só issues, C24 = não detectado, e isso bate com o resultado original. Regras próximas que geram issue: php:S4144 (métodos com implementação idêntica, Sonar way), php:S1871 (ramos idênticos, Sonar way) e php:S1192 (literais string duplicados, Sonar way).

### 4. Achado extra: o analisador HTML também lê arquivos `.php`

- No sonar-html 3.31.0.8334, `HtmlConstants.OTHER_FILE_SUFFIXES = List.of("php", "php3", "php4", "php5", "phtml", "inc", "vue")` e o `HtmlSensor` seleciona arquivos por linguagem `web`/`jsp` **ou** por essas extensões:
  - https://github.com/SonarSource/sonar-html/blob/3.31.0.8334/sonar-html-plugin/src/main/java/org/sonar/plugins/html/api/HtmlConstants.java
  - https://github.com/SonarSource/sonar-html/blob/3.31.0.8334/sonar-html-plugin/src/main/java/org/sonar/plugins/html/core/HtmlSensor.java
- Portanto, regras do repositório `Web` (perfil da linguagem **web**/HTML, não do PHP) podem gerar issues nos `.php`, mas só sobre o HTML fora de `<?php ?>`. As relevantes são `InlineStyleCheck` (C06), `UnclosedTagCheck` (C10) e `LongJavaScriptCheck` (C05, parcial). **Nenhuma delas está no Sonar way do HTML** (61 regras: https://github.com/SonarSource/sonar-html/blob/3.31.0.8334/sonar-html-plugin/src/main/resources/org/sonar/l10n/web/rules/Web/Sonar_way_profile.json).
- Para que a configuração "completa" as inclua, rode o script com `LANGUAGES="php web"`.
- **Não verificado em execução:** confirmei no código que o sensor HTML seleciona os `.php`, mas não rodei o servidor para ver os issues `Web:` em arquivos PHP. Convém confirmar no primeiro teste. As linhas `Web:` do CSV estão marcadas no campo `grupo`.
- Não verifiquei o analisador JavaScript (sonar-javascript 13.8.0.44569, também empacotado) quanto a JS embutido em `.php`. Nenhum dos 38 CS tem regra JS óbvia; C07 (CSS em JavaScript) ficou sem regra.

---

## PHPStan e Psalm: versões, configurações e evidências

Levantamento de 2026-09-25. Fontes: phpstan.org, github.com/phpstan/phpstan(-src), psalm.dev e github.com/vimeo/psalm, lidos nas tags citadas. Houve também um teste rápido: as duas ferramentas rodaram sobre um arquivo de exemplo (`teste-stan-psalm/app/Exemplo.php`), PHPStan no PHP 8.3.6 do host e Psalm num contêiner PHP 8.4.21.

### 1. Versões

| Ferramenta | Última estável | Data (UTC) | URL | PHP para executar |
|---|---|---|---|---|
| PHPStan | **2.2.16** | 2026-09-25 09:33 | https://github.com/phpstan/phpstan/releases/tag/2.2.16 | **PHP ≥ 7.4**: `"php": "^7.4\|^8.0"` no composer.json do pacote distribuído (o fonte, phpstan-src, exige ^8.2 e é rebaixado no phar). A página getting-started diz: "PHPStan requires PHP >= 7.4". |
| Psalm | **6.18.0** | 2026-09-21 11:04 | https://github.com/vimeo/psalm/releases/tag/6.18.0 | **PHP 8.1.31+ / 8.2.27+ / 8.3.16+ / 8.4.3+ / 8.5**: `"php": "~8.1.31 \|\| ~8.2.27 \|\| ~8.3.16 \|\| ~8.4.3 \|\| ~8.5.0"` no composer.json. O phar se recusa a rodar no PHP 8.3.6 do host. |

- A versão anterior do PHPStan é a 2.2.15, de 2026-09-23. A 2.2.16 saiu no dia deste levantamento. Se preferirem uma versão com alguns dias de uso, fixem a 2.2.15; as regras citadas aqui são iguais nas duas.
- A 7.0.0-beta22 do Psalm é pré-lançamento e fica de fora.
- Extensão oficial do PHPStan: phpstan/phpstan-strict-rules **2.0.12**, de 2026-07-19 (https://github.com/phpstan/phpstan-strict-rules/releases/tag/2.0.12).

**Versão do PHP do código analisado (Laravel 5.5, PHP ≥ 7.0):**
- **PHPStan.** Com `phpVersion` nulo, o PHPStan infere a faixa a partir de `require.php` do composer.json do projeto (`src/Php/ComposerPhpVersionFactory.php`; página config-reference, seção `phpVersion`). O menor valor aceito em `phpVersion` é 70100 (`conf/parametersSchema.neon`), então não dá para declarar PHP 7.0 explicitamente. As configurações deixam o parâmetro sem valor.
- **Psalm.** Sem `phpVersion`, o Psalm usa "the earliest version of PHP that satisfies the declared `php` dependency" do composer.json (docs/running_psalm/configuration.md). Para o Laravel 5.5 isso dá 7.0.

### 2. Configuração "padrão"

#### PHPStan: nível 0
- `src/Command/CommandHelper.php`: `public const DEFAULT_LEVEL = '0'`. O nível 0 só é usado quando `$projectConfigFile === null && $level === null`, isto é, sem arquivo de configuração e sem `--level`.
- A página https://phpstan.org/user-guide/rule-levels diz: "The default level is 0. Once you specify a configuration file, you also have to specify the level to run."
- Se houver um arquivo de configuração sem `level`, nenhum `config.levelN.neon` é incluído e a execução aborta com "No rules detected" (CommandHelper.php, `customRulesetUsed === null`). Por isso `phpstan-padrao.neon` declara `level: 0` explicitamente. O resultado é igual a `phpstan analyse app` sem arquivo algum.
- Na saída do nível 0 aparece a dica "PHPStan is performing only the most basic checks ... (the default and current level is 0)" (`TableErrorFormatter.php`).

#### Psalm: errorLevel omitido (nível 2) e findUnusedCode="true"
- O Psalm não roda sem configuração. Sem `psalm.xml`, ele avisa "Could not locate a config XML file ... Have you run 'psalm --init' ?" e termina (`src/Psalm/Internal/CliUtils.php`).
- `psalm --init` grava o modelo `Creator::TEMPLATE` (`src/Psalm/Config/Creator.php`), que contém:
  - `errorLevel="N"`;
  - `resolveFromConfigFile="true"`;
  - `findUnusedBaselineEntry="true"`;
  - `findUnusedCode="true"`;
  - `projectFiles` e `ignoreFiles vendor`.
- O **N depende do código.** Sem o argumento de nível, `--init` analisa o projeto e escolhe o nível com `Creator::getLevel`. Com `psalm --init app 3`, grava o nível pedido.
- Sem `errorLevel` no XML, o nível é **2**. Três fontes confirmam:
  - docs/running_psalm/error_levels.md: "When no level is explicitly defined, psalm defaults to level 2";
  - `Config.php`: `else { $config->level = 2; }`;
  - `config.xsd`: `errorLevel default="2"`.
- Por isso `psalm-padrao.xml` segue o modelo do `--init` **sem** `errorLevel`. Assim ele é reprodutível e não depende do nível que o `--init` detectaria.
  - **Alternativa:** rodar `psalm --init app` no projeto, anotar o nível detectado e usá-lo como padrão.
  - **Efeito no mapeamento:**
    - As questões de código não usado valem em qualquer nível.
    - MissingReturnType (C31) só é erro nos níveis 1–2.
    - ForbiddenCode (C01) e InvalidDocblock (C34) só são erro nos níveis 1–4.
- Padrões do Psalm 6.18.0 (`config.xsd` e `Config.php`):

  | Opção | Padrão |
  |---|---|
  | `findUnusedCode` | `true` (`public bool $find_unused_code = true`; configuration.md: "Defaults to `true`") |
  | `findUnusedVariablesAndParams` | `false` |
  | `findUnusedPsalmSuppress` | `false` |
  | `findUnusedBaselineEntry` | `true` |
  | `findUnusedIssueHandlerSuppression` | `true` |
  | `limitMethodComplexity` | `false` |
  | `maxGraphSize` | 200 |
  | `maxAvgPathLength` | 70 |

  - O padrão `false` de `findUnusedVariablesAndParams` não desliga nada na prática. Com `findUnusedCode` ativo, a CLI chama `Codebase::reportUnusedCode()`, que define `find_unused_variables = true`. Além disso, o atributo `findUnusedCode` no XML copia seu valor para `find_unused_variables` (`Config.php`). Logo, no padrão, UnusedVariable, UnusedParam e similares ficam ligados.
- **Como o nível se aplica** (`Config::getReportingLevelForFile`):
  - Uma questão com `ERROR_LEVEL` = L > 0 é **erro** quando o nível configurado é ≤ L.
  - Com nível configurado > L, vira **info**. A saída só mostra info com `--show-info=true`, que por padrão é `false`.
  - As questões com `ERROR_LEVEL` −1 ("sempre erro") e −2 (questões de funcionalidade, como Unused\*) são erro em qualquer nível, desde que a funcionalidade esteja ligada.

### 3. Configuração "completa"

#### PHPStan: `level: max`
- `max` é um alias do nível mais alto: `conf/config.levelmax.neon` inclui `config.level10.neon`. Os níveis são cumulativos.
- As regras deste mapeamento vêm do atributo `#[RegisteredRule(level: N)]` de cada classe:

  | Identificador | Regra | Nível |
  |---|---|---|
  | `constructor.unusedParameter` | UnusedConstructorParametersRule | 1 |
  | `closure.unusedUse` | — | 1 |
  | `phpDoc.parseError` | InvalidPhpDocTagValueRule | 2 |
  | `property.unused`, `property.onlyWritten`, … | UnusedPrivatePropertyRule | 4 |
  | `method.unused` | UnusedPrivateMethodRule | 4 |
  | `missingType.return` / `missingType.parameter` | Missing*TypehintRule | 6 |

- Consequência: o nível 8 do experimento original cobre o mesmo que `max` para estes CS.
- Ficam **fora** da "completa":
  - **bleedingEdge** (`conf/bleedingEdge.neon`): prévia da próxima versão principal. Para os 38 CS, a única diferença é ligar `featureToggles.unusedLabel`, isto é, a regra `label.unused` (nível 4: rótulo de goto nunca usado). Mesmo assim não acusa o uso de `goto`.
  - **phpstan-strict-rules 2.0.12**: regras de tipagem estrita (booleanos em condições, `==`, `empty()`, variáveis variáveis, backtick, casts inúteis, LSP…). A lista de classes em `src/Rules/` foi conferida na tag 2.0.12 e nenhuma corresponde aos 38 CS. Incluí-la não mudaria a cobertura.
  - **Parâmetros extras de análise estrita** (`checkUninitializedProperties`, `checkBenevolentUnionTypes`…): não correspondem aos 38 CS.
  - **Larastan** (terceiros): melhora a inferência de tipos em Laravel, mas não é oficial.

#### Psalm: `errorLevel="1"` com todas as opções de código não usado
- `findUnusedCode`, `findUnusedVariablesAndParams`, `findUnusedPsalmSuppress`, `findUnusedBaselineEntry` e `findUnusedIssueHandlerSuppression` ficam todos em `true`.
- **`limitMethodComplexity="true"` foi incluído.** É opt-in e liga ComplexMethod/ComplexFunction (`StatementsAnalyzer::checkUnreferencedVars`), com os limites padrão `maxGraphSize=200` e `maxAvgPathLength=70`. O critério é grafo > 200, caminho médio > 70 e convergência > 1,1.
  - Motivo: é a única opção opt-in do núcleo que corresponde a CS do catálogo (C18/C19, de forma aproximada).
  - Se preferirem uma "completa" que mude só o nível e as opções de código não usado, removam o atributo. Nesse caso, C18/C19 passam a "Não".
- Não foram incluídas outras opções opt-in sem relação com os 38 CS: `checkForThrowsDocblock`, `ensureArray*OffsetsExist`, `restrictReturnTypes`, `runTaintAnalysis`…
- Também não foi incluída uma lista `<forbiddenFunctions>` (por exemplo `print_r`), porque ela é uma regra escrita pelo usuário.

### 4. Cobertura (de `regras-phpstan-psalm.csv`: 80 linhas, 38 CS × 2 ferramentas)

| Ferramenta | padrão | completa |
|---|---|---|
| PHPStan | **0** CS (o nível 0 não inclui nenhuma das regras) | **5**: C25, C26, C28 (parcial), C31 (parcial), C34 |
| Psalm | **7**: C01 (parcial), C25, C26, C27, C28, C31 (parcial), C34 (parcial) | **9**: as 7 + C18, C19 (aproximados, via limitMethodComplexity) |

### 5. Verificações pedidas

- **Detecção de código duplicado no Psalm:** não existe. As questões `Duplicate*` (DuplicateMethod, DuplicateFunction, DuplicateClass, DuplicateArrayKey, DuplicateParam…) tratam de redeclaração ou de chave repetida, não de código clonado. O crédito de "Duplicate Code" ao Psalm no experimento original não tem apoio na documentação. Provavelmente foi uma questão `Duplicate*` interpretada como clone.
- **Variáveis locais e parâmetros não usados no núcleo do PHPStan:**
  - **Variável local: não.** Não há regra para isso (a busca em `src/Rules` não encontrou nenhuma). O teste confirmou nos níveis 0, 8 e max. O mais próximo é `closure.unusedUse`, para variáveis de `use()` em closure.
  - **Parâmetro: só em construtor** (`constructor.unusedParameter`, nível 1). Um parâmetro não usado de método privado ou público não foi acusado no teste.
- **goto:** nenhuma das duas ferramentas acusa o uso.
  - O PHPStan só tem `goto.labelUndefined` (nível 0) e `label.unused` (bleedingEdge).
  - O Psalm ignora `Goto_` e `Label` ("do nothing").
- **var_dump / print_r:**
  - O Psalm acusa `ForbiddenCode` para `var_dump()` e `shell_exec()` sem configuração extra (`NamedFunctionCallHandler`). `print_r()`, `dd()` e outras só entram via `<forbiddenFunctions>`. A questão é erro nos níveis 1–4 e info nos níveis 5–8: no nível 7 do experimento original ela não aparece sem `--show-info`, o que o teste confirmou.
  - No PHPStan, nada: `phpstan.dumpType` só acusa a função de depuração do próprio PHPStan.
- **catch vazio:** nenhuma das duas. O PHPStan tem `catch.neverThrown` (nível 4, "Dead catch"), que trata de exceção nunca lançada, não de bloco vazio.
- **PHPDoc ausente / @return ausente:**
  - Nenhuma das duas exige comentário.
  - `missingType.return` (PHPStan, nível 6) e `MissingReturnType` (Psalm, `ERROR_LEVEL` 2) acusam a falta de *tipo* de retorno, e um tipo nativo satisfaz a regra. Por isso C31 conta como coberto parcialmente e C30 como não coberto.
- **@param sem nome:**
  - **PHPStan:** `phpDoc.parseError`, nível 2. O phpdoc-parser 2.3.5 exige a variável (`parseRequiredVariableName`). O teste acusou tanto `@param int` quanto `@param int descrição`.
  - **Psalm:** `InvalidDocblock` ("Badly-formatted @param"), níveis 1–4, **só** quando a tag tem apenas o tipo. `@param int descrição` é ignorado em silêncio (`FunctionLikeDocblockParser`); o teste confirmou.

### 6. Observações para o experimento

1. **Psalm e classes não referenciadas.** Os membros de uma classe não referenciada não são checados: o Psalm emite `UnusedClass` e pula `checkMethodReferences`/`checkPropertyReferences` (`Codebase/ClassLikes.php`). No teste, UnusedProperty e UnusedMethod só apareceram depois de criar um arquivo que usa a classe.
   - Numa pasta de exemplos isolados, ou em controllers do Laravel 5.5 referenciados apenas por strings de rota (`'Controller@acao'`), isso pode esconder C25/C26.
   - Contorno documentado: anotar a classe com `@psalm-api`.
2. **Psalm e parâmetros não usados** (`FunctionLikeAnalyzer::checkParamReferences`):
   - Em métodos privados e funções, só são acusados os parâmetros finais não usados. A varredura vai do último para trás e para no primeiro parâmetro usado. No teste, `privadoComParam($p, $q)` com `$p` não usado não gerou aviso.
   - Em métodos públicos/protegidos não finais, a questão é `PossiblyUnusedParam`, em qualquer posição.
3. **Propriedade só escrita.** O Psalm conta uma propriedade usada apenas no construtor como não usada. O PHPStan distingue `property.unused` de `property.onlyWritten`.
4. **Sem Larastan, o PHPStan gera muito ruído no Laravel** (facades, Eloquent). Isso não muda o mapeamento, mas afeta a contagem de avisos.

### 7. Créditos do experimento original

**PHPStan, nível 8:**

| CS creditado | Situação | Base |
|---|---|---|
| Unused Private Field | consistente | property.unused / property.onlyWritten, nível 4 |
| Unused Private Method | consistente | method.unused, nível 4 |
| Missing @Return Tag | consistente em parte | missingType.return, nível 6; é falta de tipo, não de tag |
| Unused Formal Parameter | consistente só para construtores | constructor.unusedParameter, nível 1 |
| Unused Local Variable | **não consistente** | não há regra; o teste confirmou |
| Unnecessary Code, Inefficient Logic, Incorrect String Manipulation | plausíveis, não verificados um a um | não estão entre os 38 CS |

Para os três últimos, as regras mais prováveis são as de nível 4 (código morto): `deadCode.unreachable`, `*.resultUnused`, `if.alwaysTrue`/`alwaysFalse`, `function.alreadyNarrowedType`, `nullCoalesce.unnecessary`. Há ainda `argument.type` (nível 5) para funções de string.

**Psalm, errorLevel 7 com findUnusedCode:**

| CS creditado | Situação | Base |
|---|---|---|
| Unused Private Field | consistente | UnusedProperty (questão de funcionalidade, vale em qualquer nível); o teste confirmou no nível 7 |
| Unused Private Method | consistente | UnusedMethod; confirmado no nível 7 |
| Unused Local Variable | consistente | UnusedVariable; confirmado no nível 7 |
| Unused Formal Parameter | consistente | UnusedParam / PossiblyUnusedParam; confirmado no nível 7 |
| Duplicate Code | **não consistente** | não há detecção de clones |

No nível 7, ForbiddenCode, InvalidDocblock e MissingReturnType saem só como info. Isso bate com o fato de o experimento original não ter creditado ao Psalm C01, C31 e C34.

### 8. O que não foi verificado

- ComplexMethod/ComplexFunction não foram exercitadas no teste: o exemplo é pequeno demais para ultrapassar os limites. A verificação foi feita só pelo código-fonte.
- O nível que `psalm --init` detectaria no projeto Laravel real não foi apurado, porque o projeto não estava disponível.
- As regras do PHPStan ligadas aos três CS extras do experimento original (Unnecessary Code etc.) não foram mapeadas uma a uma, porque esses CS não fazem parte do catálogo de 38.
