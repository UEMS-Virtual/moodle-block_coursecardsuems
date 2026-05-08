# Fluxo para registrar requisitos e criar issues

Ordem recomendada para não perder contexto antes de abrir issues:

## 1. Ubiquitous Language

Objetivo: registrar e padronizar os termos do domínio antes de escrever requisitos.

Termos iniciais a consolidar:

- Meus cursos
- Card de curso
- Oferta
- Reoferta
- Status do curso
- Docente
- Período letivo
- Faixa lateral
- Resumo dos cursos
- Curso matriculado

Skill sugerida:

```txt
ubiquitous-language
```

Saída esperada:

```txt
UBIQUITOUS_LANGUAGE.md
```

## 2. Grill with docs

Objetivo: entrevistar e validar decisões de produto/design contra a documentação e o estado atual do plugin.

Perguntas que devem ser resolvidas:

- O card deve priorizar disciplina, curso ou disciplina + curso?
- `OFERTA` é tipo de oferta, situação acadêmica ou apenas badge visual?
- A faixa lateral representa série, área, categoria, curso ou outro agrupamento?
- O bloco `coursecardsuems` substitui totalmente o `myoverview` em Meus cursos?
- O layout deve ser fiel ao PDF de design ou apenas inspirado nele?
- O bloco precisa de filtros, busca, paginação ou agrupamentos?
- Quais dados vêm do Moodle e quais vêm do Argos?

Skill sugerida:

```txt
grill-with-docs
```

Saída esperada:

- decisões registradas em documentação do projeto;
- possíveis ADRs somente quando a decisão for difícil de reverter e não óbvia.

## 3. PRD

Objetivo: transformar termos e decisões em um documento de requisitos do produto.

Skill recomendada:

```txt
write-a-prd
```

Use `write-a-prd` quando ainda houver entrevista e refinamento.
Use `to-prd` apenas se a conversa já estiver suficientemente completa e a intenção for sintetizar rapidamente.

Saída esperada:

- PRD com problema, solução, histórias de usuário, decisões de implementação, decisões de teste e fora de escopo.

## 4. Issues

Objetivo: quebrar o PRD/plano em issues pequenas e implementáveis.

Skill recomendada:

```txt
to-issues
```

Use `prd-to-issues` apenas se o PRD já estiver publicado como issue no GitHub.

As issues devem seguir fatias verticais/tracer bullets. Exemplos prováveis:

- ajustar layout base do card;
- definir origem da faixa lateral;
- exibir metadados acadêmicos do curso;
- configurar status e tipo de oferta;
- adicionar paginação ou limite de cursos;
- adicionar filtros/busca;
- melhorar acessibilidade;
- criar testes e fixtures.

## Ordem resumida

```txt
1. ubiquitous-language
2. grill-with-docs
3. write-a-prd
4. to-issues
```

Ordem curta, se houver pressa:

```txt
1. write-a-prd
2. to-issues
```
