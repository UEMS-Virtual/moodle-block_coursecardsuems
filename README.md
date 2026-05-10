# block_coursecardsuems

Plugin Moodle do tipo bloco para exibir disciplinas EaD da UEMS Virtual em cards/listas organizados por status no semestre vigente.

## Estado atual

Este repositório contém um **protótipo visual/funcional** que validou a direção de UX, mas que será reconstruído para respeitar padrões Moodle.

A implementação atual não deve ser tratada como arquitetura final.

Leia antes de implementar:

- `CONTEXT.md` — escopo vigente do domínio;
- `docs/PROTOTYPE.md` — o que foi validado no protótipo e o que não deve ser reaproveitado sem revisão;
- `docs/adr/0001-reconstruir-plugin-apos-prototipo.md` — decisão de reconstrução.

## Escopo do produto

O bloco deve mostrar, para o aluno, apenas disciplinas **EaD** do **semestre vigente**.

O título do bloco deve ser dinâmico, por exemplo:

```text
Semestre 2026/1
```

As disciplinas são separadas por status:

- **Abertas** — seção colapsável aberta por padrão, exibida em cards.
- **Em breve** — seção colapsável fechada por padrão, exibida em lista/resumo e sem link.
- **Encerradas** — seção colapsável fechada por padrão, exibida em lista/resumo com visual apagado e clicável quando o Moodle ainda permitir acesso.

## Dados principais

- O período informativo da disciplina vem dos campos customizados:
  - `ead_inicio`
  - `ead_final`
- As datas nativas do Moodle (`course.startdate` e `course.enddate`) representam janela de acesso e não devem ser exibidas como período da disciplina.
- A janela de acesso Moodle pode ser usada como fallback provisório para status quando o período informativo estiver ausente.
- A faixa lateral usa agrupamento compacto derivado do `shortname`, como `PEDG-24`.
- A tag secundária usa a **Série**, como `2ª Série`.
- O código entre colchetes no nome do curso é exibido com menor destaque; o nome real da disciplina é o título principal.

## Links e disponibilidade

A regra final está documentada em `docs/adr/0002-links-e-visibilidade-por-status.md`.

Resumo:

- Disciplinas **Em breve** não são clicáveis.
- Disciplinas **Abertas** são clicáveis somente quando o curso Moodle estiver visível/disponível.
- Disciplinas temporalmente abertas, mas ocultas/indisponíveis porque a sala ainda não está pronta, aparecem em **Em breve** e sem link.
- Disciplinas **Encerradas** continuam clicáveis quando o Moodle permitir acesso.
- Disciplinas **Encerradas** ocultas/indisponíveis continuam em **Encerradas**, mas sem link.

## Arquitetura desejada

A versão final deve seguir convenções Moodle e separar responsabilidades:

```text
block_coursecardsuems.php
classes/
  local/
    course_repository.php
    current_semester.php
    course_status_resolver.php
    course_card_mapper.php
    category_parser.php
  output/
    renderer.php
    course_card.php
    course_section.php
templates/
  course_card.mustache
  course_list_item.mustache
  course_section.mustache
lang/
  en/
  pt_br/
tests/
```

Diretrizes:

- `block_coursecardsuems.php` deve apenas orquestrar o bloco.
- HTML não trivial deve ir para templates Mustache.
- Texto visível deve ir para arquivos de idioma.
- Regras de domínio devem ser testáveis sem depender de HTML.
- Acesso a dados deve usar APIs Moodle.
- Mudanças persistentes devem seguir `db/install.xml`, `db/upgrade.php` e bump em `version.php`.

## Desenvolvimento e validação

Este plugin segue práticas Moodle. Antes de editar código, consulte a skill/documentação de desenvolvimento Moodle disponível no ambiente do agente.

O checklist completo está em `docs/VALIDATION.md`.

Lint PHP:

```bash
find . -path ./.git -prune -o -name '*.php' -print | sort | xargs -n1 php -l
```

PHPUnit no ambiente Docker local:

```bash
MOODLE_DOCKER_CONTAINER="${MOODLE_DOCKER_CONTAINER:-moodle45-app}"

for f in tests/*_test.php; do
  docker exec -u www-data "$MOODLE_DOCKER_CONTAINER" php /var/www/html/vendor/bin/phpunit \
    --configuration /var/www/html/phpunit.xml \
    "/var/www/html/blocks/coursecardsuems/$f"
done
```

Após alterações de template/CSS, limpe cache:

```bash
MOODLE_DOCKER_CONTAINER="${MOODLE_DOCKER_CONTAINER:-moodle45-app}"

docker exec "$MOODLE_DOCKER_CONTAINER" php /var/www/html/admin/cli/purge_caches.php
```

## Fora do escopo atual

- Cursos presenciais.
- Visões específicas para tutor/docente.
- Histórico de todos os semestres.
- Substituir a visão geral nativa do Moodle.
- Busca, paginação, filtros avançados e preferências por usuário.
