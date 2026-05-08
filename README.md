# block_coursecardsuems

Bloco Moodle planejado para exibir cards de cursos personalizados para uso exclusivo da UEMS.

> **Importante:** as fases abaixo são apenas sugestões iniciais de organização do trabalho. Elas não representam uma decisão final de escopo, arquitetura ou ordem de implementação.

## Ideia geral

Criar um plugin de bloco Moodle novo, inspirado conceitualmente no `block_myoverview` / Course overview, mas com comportamento próprio e layout altamente personalizado para a universidade.

Nome técnico sugerido/adotado:

- Pasta: `blocks/coursecardsuems`
- Componente Moodle: `block_coursecardsuems`

## Sugestões de fases

### Fase 1 — Bloco novo simples

Sugestão inicial:

- criar estrutura básica do bloco Moodle;
- listar cursos vinculados ao usuário logado;
- exibir card com imagem/banner, nome e descrição;
- usar renderização server-side com PHP + Mustache;
- evitar AJAX, filtros e paginação complexa neste primeiro momento.

### Fase 2 — Campos personalizados dos cursos

Sugestão inicial:

- avaliar uso de campos personalizados de curso do Moodle;
- mapear quais campos a UEMS precisa exibir nos cards;
- exemplos possíveis:
  - banner alternativo;
  - descrição curta;
  - carga horária;
  - nível;
  - selo/badge;
  - modalidade;
  - área/trilha;
  - cor ou variação visual do card.

### Fase 3 — Configurações do bloco

Sugestão inicial:

- permitir configurar quantidade máxima de cursos exibidos;
- escolher ordenação;
- decidir se cursos finalizados, futuros ou ocultos aparecem;
- permitir selecionar quais campos aparecem no card;
- avaliar filtros por categoria, campo personalizado ou status do curso.

### Fase 4 — Comportamentos avançados

Sugestão inicial, caso seja necessário:

- busca;
- paginação;
- filtros dinâmicos;
- agrupamentos por status do curso;
- favoritos;
- ocultar curso no bloco;
- preferências por usuário;
- carregamento assíncrono via AMD/AJAX.

## Decisões ainda em aberto

- O bloco aparecerá apenas no Dashboard, em Meus cursos ou em outras páginas também?
- Deve mostrar apenas cursos inscritos do usuário ou também cursos disponíveis?
- A descrição virá do resumo padrão do curso ou de um campo personalizado específico?
- O banner usará a imagem padrão do curso ou um campo próprio?
- Quais campos personalizados são obrigatórios para a UEMS?
- O bloco precisa reproduzir algum comportamento do Course overview original?

## Observação arquitetural

A ideia inicial é criar um plugin novo e independente, usando o `block_myoverview` apenas como referência de comportamento e APIs quando fizer sentido, evitando herança direta do bloco core.
