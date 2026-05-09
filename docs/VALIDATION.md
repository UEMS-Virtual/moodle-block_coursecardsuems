# Validação da reconstrução Moodle

Este checklist registra a validação final da reconstrução do `block_coursecardsuems`.

## PHPUnit

Execute cada teste do plugin dentro do container Moodle:

```bash
for f in tests/*_test.php; do
  docker exec -u www-data moodle45-app php /var/www/html/vendor/bin/phpunit \
    --configuration /var/www/html/phpunit.xml \
    "/var/www/html/blocks/coursecardsuems/$f"
done
```

Cobertura atual:

| Arquivo | Foco |
| --- | --- |
| `tests/current_semester_test.php` | Semestre vigente e limites de semestre. |
| `tests/course_shortname_parser_test.php` | Parsing do `shortname` de disciplinas UEMS. |
| `tests/course_filter_test.php` | Filtro de disciplina EaD do semestre vigente. |
| `tests/informative_period_reader_test.php` | Leitura de `ead_inicio` e `ead_final`. |
| `tests/course_status_resolver_test.php` | Status temporal e ordenação por status. |
| `tests/course_card_mapper_test.php` | View model, disponibilidade, links e curso oculto. |
| `tests/summary_test.php` | Seções Abertas, Em breve e Encerradas. |
| `tests/course_repository_test.php` | Repositório de cursos matriculados. |

## Lint PHP

```bash
find . -path ./.git -prune -o -name '*.php' -print | sort | xargs -n1 php -l
```

## Checklist Moodle manual

- Bloco (`block_coursecardsuems.php`) apenas orquestra serviços e renderização.
- Dados são obtidos por APIs Moodle (`enrol_get_my_courses`, custom fields API, categorias, papéis).
- Regras de domínio ficam em `classes/local/` e possuem testes PHPUnit.
- Saída HTML principal fica em template Mustache (`templates/summary.mustache`).
- Textos visíveis ficam em `lang/en` e `lang/pt_br`.
- Variáveis do template usam escaping Mustache padrão (`{{var}}`); não há HTML arbitrário injetado por `{{{var}}}`.
- Links para cursos só são renderizados quando `hasurl=true`.
- Itens não clicáveis são renderizados como `<article aria-disabled="true">`.
- Links clicáveis têm `aria-label` localizado.
- Status e seções seguem as regras documentadas em `CONTEXT.md` e ADR-0002.
- Não há Behat neste fechamento: o comportamento visual/interativo foi validado por Playwright local e as regras foram cobertas em PHPUnit.

## Validação visual local

Após alterações de template/CSS:

```bash
docker exec moodle45-app php /var/www/html/admin/cli/purge_caches.php
```

Validar no dashboard local que:

- `Abertas` abre por padrão e usa grid;
- `Em breve` começa fechada, usa lista e não tem links;
- `Encerradas` começa fechada, usa lista apagada e mantém links quando disponíveis;
- disciplina aberta pelas datas, mas oculta no Moodle, aparece em `Em breve` sem link e com a mensagem `Disponível em breve`.
