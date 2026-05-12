> **Documento histórico** — estudos de design anteriores às issues #17/#18. O design aprovado usa ribbon fina via `::before`, supertítulo e sem código em destaque.

# Decisão de design dos cards

## Referência visual

Os estudos visuais estão em:

- `docs/images/first_design_pages-to-jpg-0001.jpg` — padrão 01: card clássico com capa/banner + badge;
- `docs/images/first_design_pages-to-jpg-0002.jpg` — continuação do padrão 01;
- `docs/images/first_design_pages-to-jpg-0003.jpg` — padrão 02: card sem capa, com faixa lateral por categoria;
- `docs/images/first_design_pages-to-jpg-0004.jpg` — continuação do padrão 02;
- `docs/images/first_design_pages-to-jpg-0005.jpg` — padrão 03: modo lista/resumo;
- `docs/images/first_design_pages-to-jpg-0006.jpg` — continuação do padrão 03.

## Padrão escolhido

O padrão inicial escolhido para o plugin é o **Padrão 02 — card sem capa, com faixa lateral por categoria**.

## Justificativa

A decisão é remover o banner/capa do curso para aproveitar melhor o espaço disponível no bloco. O padrão 02 concentra mais informação útil por área visível e evita que a imagem ocupe uma parte grande do card sem necessariamente agregar valor funcional.

## Diretrizes visuais iniciais

Cada card deve priorizar:

- faixa lateral usando inicialmente a cor principal herdada do tema/site;
- texto vertical indicando uma categoria, área ou agrupamento visual do curso;
- período/semestre destacado na parte inferior da faixa lateral;
- título do curso em destaque;
- indicador de tipo de oferta, como `OFERTA` ou `REOFERTA`;
- status do curso, como `ABERTA`, `EM BREVE` ou `ENCERRADA`;
- docentes em destaque, com avatar/iniciais;
- datas de início e término na base do card;
- layout em duas colunas quando houver largura suficiente.

## Elementos removidos do escopo visual inicial

- Banner/capa/imagem do curso no topo do card.
- Dependência visual da imagem padrão do curso Moodle.

## Implicações para requisitos

Como o design escolhido não usa banner, a primeira versão do plugin não precisa depender de imagem padrão do curso nem de campo personalizado de imagem.

A cor da faixa lateral será simplificada na primeira versão: deve herdar a cor principal do tema/site. Mapeamentos por categoria, campo personalizado ou configuração ficam como melhoria futura, a ser discutida em conjunto com a equipe.

O texto vertical da faixa lateral teoricamente representa uma categoria, área ou agrupamento do curso. Porém, como a árvore de categorias da UEMS é complexa e ainda não possui padrão confiável, a primeira versão não deve depender rigidamente de um nível específico da árvore. Uma possibilidade futura é usar um campo personalizado no curso para alimentar esse rótulo de forma controlada.

O período/semestre pode ser calculado a partir do intervalo entre data de início e data de término da disciplina, mas também pode vir de um campo preenchido com dados do sistema acadêmico da UEMS. A origem final ainda precisa ser validada.

## Perguntas em aberto sobre o design

1. Qual variável/cor do tema Moodle deve ser usada na faixa lateral?
2. O texto vertical virá de qual fonte: nível específico da árvore de categorias, campo personalizado ou outro dado institucional?
3. Se vier da árvore de categorias, qual nível deve ser considerado?
4. O período/semestre será calculado pelas datas do curso/disciplina ou virá de campo preenchido por integração com o sistema acadêmico?
5. O código curto exibido abaixo do docente, como `PED-204`, `LET-312`, `TEST-000`, será necessário?
6. Quantos docentes devem aparecer antes de usar `+ N outros`?
7. Em telas menores, o layout vira uma coluna mantendo a faixa lateral?
