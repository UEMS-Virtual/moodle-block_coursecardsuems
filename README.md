# block_coursecardsuems

Plugin Moodle do tipo bloco que exibe disciplinas EaD da UEMS Virtual em cards/listas organizados por status no semestre vigente.

## Escopo

O bloco mostra, para o aluno, apenas disciplinas **EaD** do **semestre vigente**, separadas por status em abas:

- **Abertas** — exibidas em cards (grid). Clicáveis quando o curso Moodle estiver visível.
- **Em breve** — exibidas em lista. Nunca clicáveis.
- **Encerradas** — exibidas em lista, tom apagado. Clicáveis quando o Moodle ainda permitir acesso.

Disciplina temporalmente aberta mas oculta/indisponível no Moodle aparece em **Em breve**, sem link.

## Dados exibidos

- **Período informativo**: campos customizados `ead_inicio` e `ead_final`.
- `course.startdate`/`course.enddate` são usados só como fallback de status quando o período informativo estiver ausente.
- **Supertítulo**: agrupamento compacto do `shortname` (ex: `PEDG-24`) + série da categoria Moodle.
- **Reoferta**: identificada pelo `shortname` (presença de `REO`).
- **Docente**: primeiro usuário com papel `editingteacher` ou `teacher`.

Regra completa de links e visibilidade: `docs/adr/0002-links-e-visibilidade-por-status.md`.

## Arquitetura

```
block_coursecardsuems.php       — orquestra o bloco
classes/
  local/
    course_repository.php       — busca cursos matriculados
    current_semester.php        — calcula semestre vigente
    course_status_resolver.php  — resolve status (open/comingsoon/closed)
    course_card_mapper.php      — monta view model de cada disciplina
    informative_period_reader.php
    course_filter.php
    course_shortname_parser.php
    category_parser.php
  output/
    summary.php                 — renderable principal (agrupa seções)
    renderer.php
amd/src/
  section_tabs.js               — gerencia abas no frontend
templates/
  summary.mustache              — layout de abas e painéis
  card.mustache                 — card de disciplina (grid)
lang/en/ lang/pt_br/
tests/
```

## Fora do escopo

- Cursos presenciais.
- Visão de tutor/docente.
- Histórico de semestres anteriores.
- Busca, paginação, filtros e preferências por usuário.

## Validação

Ver `docs/VALIDATION.md` para o checklist completo.

Lint PHP:

```bash
find . -path ./.git -prune -o -name '*.php' -print | sort | xargs -n1 php -l
```

PHPUnit no Docker:

```bash
MOODLE_DOCKER_CONTAINER="${MOODLE_DOCKER_CONTAINER:-moodle45-app}"

for f in tests/*_test.php; do
  docker exec -u www-data "$MOODLE_DOCKER_CONTAINER" php /var/www/html/vendor/bin/phpunit \
    --configuration /var/www/html/phpunit.xml \
    "/var/www/html/blocks/coursecardsuems/$f"
done
```

Purge de cache após alterações de template/CSS:

```bash
docker exec "${MOODLE_DOCKER_CONTAINER:-moodle45-app}" php /var/www/html/admin/cli/purge_caches.php
```
