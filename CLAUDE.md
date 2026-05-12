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

Issue aberta de funcionalidade:

- `#20 Adicionar contagem de Agendas nos cards`

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

## Variáveis de ambiente recomendadas

O nome do container Docker, URL local e credenciais mudam por ambiente. Antes de rodar validações, configure variáveis em vez de hardcodar valores:

```bash
export MOODLE_DOCKER_CONTAINER="${MOODLE_DOCKER_CONTAINER:-moodle45-app}"
export MOODLE_URL="${MOODLE_URL:-http://localhost:8080}"
export MOODLE_TEST_USERNAME="${MOODLE_TEST_USERNAME:-admin}"
export MOODLE_TEST_PASSWORD="${MOODLE_TEST_PASSWORD:-admin}"
```

Se o ambiente não usar `admin/admin`, defina `MOODLE_TEST_USERNAME` e `MOODLE_TEST_PASSWORD` explicitamente antes dos scripts Playwright.

## Comandos de validação

### Lint PHP

```bash
find . -path ./.git -prune -o -name '*.php' -print | sort | xargs -n1 php -l
```

### PHPUnit do plugin no Docker local

```bash
MOODLE_DOCKER_CONTAINER="${MOODLE_DOCKER_CONTAINER:-moodle45-app}"

for f in tests/*_test.php; do
  docker exec -u www-data "$MOODLE_DOCKER_CONTAINER" php /var/www/html/vendor/bin/phpunit \
    --configuration /var/www/html/phpunit.xml \
    "/var/www/html/blocks/coursecardsuems/$f"
done
```

Para rodar um teste específico:

```bash
MOODLE_DOCKER_CONTAINER="${MOODLE_DOCKER_CONTAINER:-moodle45-app}"

docker exec -u www-data "$MOODLE_DOCKER_CONTAINER" php /var/www/html/vendor/bin/phpunit \
  --configuration /var/www/html/phpunit.xml \
  /var/www/html/blocks/coursecardsuems/tests/<arquivo>_test.php
```

### Purge de cache Moodle

Após alterações em PHP/template/CSS/strings:

```bash
MOODLE_DOCKER_CONTAINER="${MOODLE_DOCKER_CONTAINER:-moodle45-app}"

docker exec "$MOODLE_DOCKER_CONTAINER" php /var/www/html/admin/cli/purge_caches.php
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

Use variáveis para URL e login:

```bash
export MOODLE_URL="${MOODLE_URL:-http://localhost:8080}"
export MOODLE_TEST_USERNAME="${MOODLE_TEST_USERNAME:-admin}"
export MOODLE_TEST_PASSWORD="${MOODLE_TEST_PASSWORD:-admin}"
```

Exemplo base de script Playwright:

```js
const { chromium } = require('playwright');

const baseUrl = process.env.MOODLE_URL || 'http://localhost:8080';
const username = process.env.MOODLE_TEST_USERNAME || 'admin';
const password = process.env.MOODLE_TEST_PASSWORD || 'admin';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });

  await page.goto(`${baseUrl}/my/`, { waitUntil: 'networkidle' });

  if (page.url().includes('/login') || await page.locator('input[name="username"]').count()) {
    await page.fill('input[name="username"]', username);
    await page.fill('input[name="password"]', password);
    await Promise.all([
      page.waitForLoadState('networkidle').catch(() => {}),
      page.press('input[name="password"]', 'Enter'),
    ]);
  }

  await page.waitForTimeout(1000);

  const sections = await page.locator('.block_coursecardsuems .coursecardsuems-section')
    .evaluateAll(els => els.map(e => ({
      title: e.querySelector('.coursecardsuems-section-title-text')?.textContent.trim(),
      count: e.querySelector('.coursecardsuems-section-count')?.textContent.trim(),
      open: e.hasAttribute('open'),
      cards: e.querySelectorAll('.coursecardsuems-card').length,
      links: e.querySelectorAll('a.coursecardsuems-card').length,
      disabled: e.querySelectorAll('.coursecardsuems-card-disabled').length,
      grid: !!e.querySelector('.coursecardsuems-grid'),
      list: !!e.querySelector('.coursecardsuems-list'),
    })));

  console.log(JSON.stringify(sections, null, 2));

  await page.locator('.block_coursecardsuems').first()
    .screenshot({ path: '/tmp/coursecards-current.png' });

  await browser.close();
})();
```

## Regras importantes

- Não usar datas Moodle nativas como período exibido da disciplina.
- `course.startdate`/`course.enddate` só podem servir como fallback de status quando não houver `ead_inicio`/`ead_final`.
- `Em breve` não é clicável.
- `Aberta` é clicável apenas se visível/disponível no Moodle.
- Disciplina aberta por data, mas oculta, aparece em `Em breve`, sem link, com mensagem `Disponível em breve`.
- `Encerrada` é clicável quando Moodle permitir; se oculta, fica sem link.
- Textos visíveis devem ir para `lang/en` e `lang/pt_br`.
- HTML principal deve ficar em Mustache, não montado manualmente em PHP.
