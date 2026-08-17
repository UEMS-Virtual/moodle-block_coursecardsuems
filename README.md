# block_coursecardsuems

Plugin Moodle do tipo bloco que exibe disciplinas EaD da UEMS Virtual em cards/listas organizados por status no semestre vigente.

## Escopo

O bloco mostra, para o aluno, apenas disciplinas **EaD** do **semestre vigente**, separadas por status em abas:

- **Abertas** — exibidas em cards (grid). Clicáveis quando o curso Moodle estiver visível.
- **Em breve** — exibidas em lista. Nunca clicáveis.
- **Últimas atividades** — disciplinas cujo período informativo terminou; exibidas em lista, tom apagado. Clicáveis quando o Moodle ainda permitir acesso.

Disciplina temporalmente aberta mas oculta/indisponível no Moodle aparece em **Em breve**, sem link.

## Público e permissões

O bloco é uma visão voltada ao **aluno**. Para usuários comuns, a listagem considera apenas cursos em que o usuário tenha a capability `block/coursecardsuems:viewcontent` no contexto do curso, concedida por padrão ao papel `student`. Permissões amplas de manager não transformam o bloco em visão administrativa.

Administradores do site são exceção operacional: veem todas as disciplinas EaD do semestre vigente, mesmo sem matrícula, para inspeção e suporte. A classificação visual continua seguindo as regras do aluno, mas os links são liberados para acesso administrativo inclusive em cursos ocultos.

Se um usuário comum não tiver nenhuma disciplina elegível como estudante, o bloco renderiza vazio.

Para administradores, o bloco também funciona como auditoria de cadastro: disciplinas EaD reconhecidas pelo shortname/categoria podem aparecer mesmo sem `ead_inicio`/`ead_final`, com aviso de período informativo ausente.

## Quando uma disciplina aparece

### Para aluno

A disciplina aparece somente se passar por todos os filtros abaixo:

1. O usuário está matriculado no curso.
2. O usuário tem a capability `block/coursecardsuems:viewcontent` no contexto do curso (por padrão, papel `student`).
3. A categoria do curso está dentro de uma árvore que contém uma categoria chamada `Distância`.
4. O `shortname` segue um formato reconhecido:
   - graduação: `PEDG_24_2S_D_df970`, `CISOL_20_4S_TEA_(REO2)_f1c8e`;
   - pós: `PGGU_T24_GARC`, `PGSP_T25_DEJDF_(REO2)_abc12`.
5. O curso tem `ead_inicio` ou `ead_final` preenchido.
6. O período `ead_inicio` → `ead_final` cruza o semestre vigente.

Se `ead_inicio` e `ead_final` estiverem vazios, a disciplina não aparece para aluno.

### Para admin do site

O admin vê uma visão de inspeção/auditoria. A disciplina aparece se:

1. A categoria do curso está dentro de uma árvore que contém uma categoria chamada `Distância`.
2. O `shortname` segue um formato reconhecido.
3. Se houver `ead_inicio` ou `ead_final`, o período cruza o semestre vigente.
4. Se não houver `ead_inicio` nem `ead_final`, a disciplina ainda aparece para auditoria com o aviso `Sem período informativo`.

Admin não precisa estar matriculado no curso e os cards ficam clicáveis mesmo para cursos ocultos.

## Dados exibidos

- **Período informativo**: campos customizados `ead_inicio` e `ead_final`.
- `course.startdate`/`course.enddate` são usados só como fallback de status quando o período informativo estiver ausente.
- **Supertítulo**: agrupamento compacto do `shortname` (ex: `PEDG-24`) + série da categoria Moodle.
- **Reoferta**: identificada pelo `shortname` (presença de `REO`).
- **Docente**: usuários com papel `editingteacher`, `teacher` ou `mod_prof`.
- **Avaliação pendente**: disciplina em **Últimas atividades** com atividade datada futura ou ainda aberta exibe o rótulo do item como **Avaliação**, mantendo o status interno `closed`.
- **Cor da faixa**: pode ser configurada nas configurações do plugin por JSON de siglas para cores hexadecimais. Exemplo: `{"PEDG24":"#ec407a","PEDG24-REO":"#f8bbd0"}`. Chaves como `PEDG24`, `PEDG-24` e `PEDG_24` são normalizadas; para `REO` e `REO2`, use `-REO`. Sem cor válida, a faixa usa o azul padrão.

Regra completa de links e visibilidade: `docs/adr/0002-links-e-visibilidade-por-status.md`.

## Arquitetura

```
block_coursecardsuems.php       — orquestra o bloco
classes/
  local/
    course_repository.php       — busca cursos matriculados
    current_semester.php        — calcula semestre vigente
    course_status_resolver.php  — resolve status (open/comingsoon/closed)
    course_activity_resolver.php — detecta atividades datadas futuras/abertas
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

## Webservice de diagnóstico

A função REST `block_coursecardsuems_get_course_diagnostics` informa se uma disciplina seria exibida para um usuário e qual foi a primeira etapa de exclusão. O serviço dedicado `block_coursecardsuems_diagnostics` é instalado **desabilitado** e restrito a usuários autorizados.

Para habilitar com segurança:

1. Em `Administração do site > Servidor > Serviços web > Serviços externos`, habilite `Course Cards UEMS diagnostics`.
2. Conceda a capability de sistema `block/coursecardsuems:viewdiagnostics` somente à conta administrativa de suporte.
3. Adicione essa conta aos usuários autorizados do serviço.
4. Gere um token específico para o serviço e revogue-o quando o diagnóstico terminar.

Exemplo sem token real:

```bash
export MOODLE_URL='https://ead.example/moodle'
export MOODLE_WS_TOKEN='substitua-pelo-token'

curl --silent --show-error --request POST \
  "$MOODLE_URL/webservice/rest/server.php" \
  --data-urlencode "wstoken=$MOODLE_WS_TOKEN" \
  --data-urlencode 'moodlewsrestformat=json' \
  --data-urlencode 'wsfunction=block_coursecardsuems_get_course_diagnostics' \
  --data-urlencode 'courseid=13289' \
  --data-urlencode 'userid=123' \
  --data-urlencode 'perspective=student'
```

`courseid` é obrigatório. `userid` usa o usuário do token quando omitido. `perspective` aceita `student`, `tutor`, `teacher` ou `admin`; quando omitida, o plugin usa a perspectiva padrão resolvida para o usuário. A resposta inclui os gates `repository`, `category`, `shortname`, `period`, `perspective` e `access`, além de `included` e `excludedat`.

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
