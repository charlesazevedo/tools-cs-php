# Variantes dos CS (item L7)

Cada um dos 38 CS de `../../dados/cs-experimento.csv` tem 2 ou 3 variantes
(decisão D4). Cada execução do experimento (L6) aplica **uma** variante sobre o
PDVLaravel original (commit `98cddb84521765c29ff7ed691d7f5ba325cc7edc`, sem
modificações) e roda as cinco ferramentas. A linha de base é o mesmo projeto sem
variante nenhuma.

## Estrutura

```
exemplos/
  variantes.csv                     uma linha por variante (metadados, abaixo)
  C13-excessive-parameter-list/
    v1/
      membros.php                   membros a inserir na CaixaController (tipo membros)
    v2/
      arquivos/app/Experimento/...  arquivos novos, no caminho relativo à raiz do projeto (tipo arquivos)
  gerar/                            scripts que geram as variantes grandes (ex.: classe de 2.000 linhas)
```

O nome da pasta do CS é `Cxx-` + o nome do CS em minúsculas, com hífens.

## Tipos de injeção

- **`membros`** — CS de método ou de trecho de código. `membros.php` é um
  arquivo PHP válido com uma única classe, `class Fragmento { ... }`; o que está
  entre as chaves da classe é inserido na `CaixaController`
  (`app/Http/Controllers/Admin/CaixaController.php`) **imediatamente antes da
  chave que fecha a classe**. Assim, as linhas da linha de base não mudam de
  número, e os avisos das linhas inseridas são identificáveis. O fragmento só
  pode usar as classes que a `CaixaController` já importa (`Request`,
  `Controller`, `QueryException`, `Sistema`, `Caixa`, `Sangria`, `Transacoes`,
  `Venda`, `Estoque`, `Entrada_caixa`) ou nomes totalmente qualificados.
- **`arquivos`** — CS de classe (tamanho da classe, contagens de membros,
  complexidade da classe, acoplamento, herança) e CS que precisam de vários
  arquivos. Tudo o que está em `arquivos/` é copiado para a raiz do projeto,
  mantendo o caminho. Classes novas ficam em `app/Experimento/<Cxx>/<Vn>/`,
  com o namespace `App\Experimento\<Cxx>\<Vn>` (PSR-4 do projeto: `App\` →
  `app/`). A `CaixaController` não é usada para CS de classe porque ela já
  dispara, na linha de base, regras de classe (ex.: tem 12 métodos públicos).
- **`membros+arquivos`** — as duas coisas (ex.: método que renderiza uma view nova).

Views novas ficam em `resources/views/experimento/<cxx>-<vn>-<nome>.blade.php`.

## Regras para escrever uma variante

1. **A variante é derivada da definição do CS**, não do comportamento das
   ferramentas. É proibido rodar PHPMD, PHPCS, PHPStan, Psalm ou SonarQube
   nas variantes durante a L7: ajustar o exemplo até a ferramenta acusar
   enviesaria o experimento. A única verificação permitida é `php -l`.
2. **Limiares.** Para CS cuja definição no catálogo tem um número ("mais de
   N"), a v1 tem o menor valor que satisfaz a definição (N + 1) e a v2 tem
   2N. A medida exata vai na coluna `medida`. Os limiares das ferramentas
   (`../../dados/regras.csv`) servem só para registrar o que se espera, não para
   dimensionar as variantes.
3. **Formas.** Para CS sem número, cada variante é uma forma diferente do
   mesmo smell (ex.: `var_dump()` e `print_r()`).
4. **Isolamento.** Fora o próprio CS, o código injetado deve ser limpo:
   - sintaxe compatível com PHP 7.0, como o resto do projeto (sem propriedades tipadas, tipos de união, promoção de propriedades no construtor, `??=`, *arrow functions*, `match`, `enum`, atributos);
   - formatação PSR-12, com indentação de 4 espaços;
   - comentário de documentação completo em cada classe e método (descrição, `@param` com tipo e nome, `@return`), salvo quando o CS é justamente de documentação ou estilo;
   - métodos com complexidade baixa, salvo quando o CS é de complexidade;
   - nomes que não sejam *getters*/*setters* (`get*`, `set*`, `is*`, `has*`), que algumas regras ignoram;
   - sem código não usado, salvo quando o CS é de código não usado.
   
   Quando o isolamento é impossível (ex.: uma classe de 2.000 linhas tem, inevitavelmente, trechos parecidos), registre em `observacao`.
5. **Realismo.** O código usa o domínio do PDVLaravel (caixa, vendas,
   estoque, sangria, clientes) e as classes do projeto.
6. **Exemplos antigos** (`../../docs/experimento-original/codesmells/`, fora do
   git) podem ser adaptados quando forem instâncias válidas do CS; a coluna
   `origem` registra de qual arquivo veio. Não copie código do PDVLaravel para
   as variantes.

## `variantes.csv`

| coluna | conteúdo |
|---|---|
| `cs_id` | C01–C38 |
| `variante` | `v1`, `v2`, `v3` |
| `forma` | descrição curta da variante, em português |
| `medida` | valor que a variante atinge, quando o CS tem limiar (ex.: `11 parâmetros`); vazio nos demais |
| `tipo` | `membros`, `arquivos` ou `membros+arquivos` |
| `arquivos` | caminhos dos arquivos da variante, relativos à pasta da variante, separados por `; ` |
| `origem` | `novo` ou `adaptado de codesmells/<arquivo>` |
| `esperado_padrao` | regras que, pela documentação (`dados/regras.csv`), devem acusar a variante na configuração padrão, no formato `Ferramenta:regra`, separadas por `; `; vazio se nenhuma |
| `esperado_completa` | o mesmo, na configuração completa |
| `observacao` | limitações do isolamento, dúvidas sobre a definição, avisos para a execução |

O que se espera **não** entra no critério de detecção. Serve para comparar,
depois da execução, o documentado com o observado.
