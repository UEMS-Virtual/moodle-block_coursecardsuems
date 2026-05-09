# ADR-0001 — Reconstruir o plugin após validação do protótipo

## Status

Aceita.

## Contexto

A implementação atual de `block_coursecardsuems` evoluiu rapidamente para validar experiência visual e regras iniciais com dados reais do Moodle. Ela ajudou a aproximar o design desejado, mas concentrou responsabilidades demais em `block_coursecardsuems.php`:

- busca de cursos;
- leitura de campos personalizados;
- parsing de categorias e shortnames;
- cálculo de semestre vigente;
- cálculo de status;
- montagem de HTML;
- decisões de layout e comportamento por status.

Isso não respeita a arquitetura esperada para plugins Moodle mantíveis. A versão final precisa seguir convenções Moodle: renderers, templates Mustache, classes em `classes/`, strings de idioma, testes e separação clara entre domínio, dados e apresentação.

Além disso, o escopo do produto mudou durante a prototipação: o bloco deve focar disciplinas EaD do semestre vigente, não uma visão genérica de todos os cursos do usuário.

## Decisão

Tratar o código atual como **protótipo visual/funcional descartável**.

A versão final do plugin será reconstruída a partir de uma arquitetura limpa, usando o protótipo apenas como referência para:

- direção visual;
- comportamento das seções;
- nomenclatura validada;
- exemplos de dados reais;
- aprendizado sobre campos customizados e árvore de categorias.

A implementação final deve evitar portar código sem revisão arquitetural.

## Consequências

- Refatorações incrementais no código atual não são a estratégia principal.
- Novas funcionalidades devem ser planejadas como fatias da reconstrução.
- O `CONTEXT.md` e o `README.md` passam a descrever o escopo vigente, não o histórico completo do protótipo.
- Decisões antigas conflitantes devem ser removidas ou registradas apenas em `docs/PROTOTYPE.md`.
- A primeira entrega técnica da reconstrução deve ser um tracer bullet Moodle correto, ainda que visualmente simples.
