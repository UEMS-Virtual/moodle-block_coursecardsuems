# CLAUDE.md

Instruções para Claude Code e outros agentes que trabalharem neste repositório.

## Leitura obrigatória

Antes de editar código, leia:

- `AGENTS.md` — instruções gerais para agentes neste projeto.
- `CONTEXT.md` — escopo vigente e linguagem de domínio.
- `README.md` — visão geral do plugin.
- `docs/VALIDATION.md` — comandos e checklist de validação.
- `docs/adr/0001-reconstruir-plugin-apos-prototipo.md` — decisão de reconstrução pós-protótipo.
- `docs/adr/0002-links-e-visibilidade-por-status.md` — regra final de links/visibilidade.
- `docs/agents/issue-tracker.md` — como operar GitHub Issues neste WSL.
- `docs/agents/triage-labels.md` — labels de triagem em português.

## Contexto rápido

Este é um plugin Moodle block: `block_coursecardsuems`.

Objetivo atual:

- mostrar disciplinas EaD do semestre vigente para aluno;
- usar período informativo dos campos customizados `ead_inicio` e `ead_final`;
- organizar em `Abertas`, `Em breve`, `Encerradas`;
- respeitar arquitetura Moodle: `classes/local`, `classes/output`, templates Mustache, strings em `lang/`, PHPUnit.

A reconstrução de dados/regras foi concluída. Existe débito visual aberto em issue:

- `#15 Refinar layout visual dos cards e listas após reconstrução`

## GitHub Issues

Neste WSL, prefira `curl` com token do `gh`, pois o comando `gh` pode falhar com DNS.

Exemplo:

```bash
OWNER='UEMS-Virtual'
REPO='moodle-block_coursecardsuems'
TOKEN="$(gh auth token)"
API="https://api.github.com/repos/$OWNER/$REPO"

curl -sS \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/vnd.github+json" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  "$API/issues?state=open&per_page=100"
```

## Comandos de validação

### Lint PHP

```bash
find . -path ./.git -prune -o -name '*.php' -print | sort | xargs -n1 php -l
```

### PHPUnit do plugin no Docker local

```bash
for f in tests/*_test.php; do
  docker exec -u www-data moodle45-app php /var/www/html/vendor/bin/phpunit \
    --configuration /var/www/html/phpunit.xml \
    "/var/www/html/blocks/coursecardsuems/$f"
done
```

Para rodar um teste específico:

```bash
docker exec -u www-data moodle45-app php /var/www/html/vendor/bin/phpunit \
  --configuration /var/www/html/phpunit.xml \
  /var/www/html/blocks/coursecardsuems/tests/<arquivo>_test.php
```

### Purge de cache Moodle

Após alterações em PHP/template/CSS/strings:

```bash
docker exec moodle45-app php /var/www/html/admin/cli/purge_caches.php
```

## Playwright / validação visual

A ferramenta Chrome do agente pode falhar se Chrome não estiver instalado em `/opt/google/chrome/chrome`.

Workaround usado neste ambiente:

- scripts em `/tmp/pwtest`;
- Playwright local via Node;
- screenshots salvos em `/tmp`, por exemplo:
  - `/tmp/coursecards-current-bug.png`
  - `/tmp/coursecards-current-fixed3.png`
  - `/tmp/coursecards-comingsoon-fixed2.png`

## Regras importantes

- Não usar datas Moodle nativas como período exibido da disciplina.
- `course.startdate`/`course.enddate` só podem servir como fallback de status quando não houver `ead_inicio`/`ead_final`.
- `Em breve` não é clicável.
- `Aberta` é clicável apenas se visível/disponível no Moodle.
- Disciplina aberta por data, mas oculta, aparece em `Em breve`, sem link, com mensagem `Disponível em breve`.
- `Encerrada` é clicável quando Moodle permitir; se oculta, fica sem link.
- Textos visíveis devem ir para `lang/en` e `lang/pt_br`.
- HTML principal deve ficar em Mustache, não montado manualmente em PHP.
