# Issue tracker: GitHub

Issues e PRDs deste repo vivem como GitHub Issues.

Preferência operacional neste ambiente: use `curl` contra a API REST do GitHub com `$(gh auth token)` quando precisar criar/editar/listar issues. O binário `gh` está autenticado, mas pode falhar de forma intermitente no WSL com erro de DNS para `api.github.com` (`lookup api.github.com on 10.255.255.254:53: no such host`). Nesses casos, `curl` funciona de forma mais estável.

Use `gh` apenas quando ele estiver respondendo normalmente; se falhar com erro de conexão/DNS, não insista — use `curl`.

## Convenções com `curl`

Defina variáveis antes de operar:

```bash
OWNER="UEMS-Virtual"
REPO="moodle-block_coursecardsuems"
TOKEN="$(gh auth token)"
API="https://api.github.com/repos/$OWNER/$REPO"
```

- **Listar issues**:

```bash
curl -sS \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/vnd.github+json" \
  "$API/issues?state=open&per_page=100"
```

- **Criar issue**: escreva o corpo em arquivo e envie JSON gerado por Python para evitar problemas de escape.

```bash
cat > /tmp/body.md <<'EOF'
## What to build

...
EOF

python3 - <<'PY' >/tmp/issue.json
import json
body = open('/tmp/body.md', encoding='utf-8').read()
print(json.dumps({
    'title': 'Título da issue',
    'body': body,
    'labels': ['pronto-para-agente'],
}, ensure_ascii=False))
PY

curl -sS \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/vnd.github+json" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  -X POST \
  --data-binary @/tmp/issue.json \
  "$API/issues"
```

- **Editar issue**:

```bash
curl -sS \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/vnd.github+json" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  -X PATCH \
  --data-binary @/tmp/issue.json \
  "$API/issues/<number>"
```

- **Criar label**:

```bash
python3 - <<'PY' >/tmp/label.json
import json
print(json.dumps({
    'name': 'pronto-para-agente',
    'color': 'ededed',
    'description': 'Totalmente especificada, pronta para agente AFK',
}, ensure_ascii=False))
PY

curl -sS \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/vnd.github+json" \
  -H "X-GitHub-Api-Version: 2022-11-28" \
  -X POST \
  --data-binary @/tmp/label.json \
  "$API/labels"
```

## Convenções com `gh` quando estiver funcionando

- **Criar issue**: `gh issue create --title "..." --body "..."`. Use heredoc para corpos multilinha.
- **Ler issue**: `gh issue view <number> --comments`, incluindo comentários e labels.
- **Listar issues**: `gh issue list --state open --json number,title,body,labels,comments --jq '[.[] | {number, title, body, labels: [.labels[].name], comments: [.comments[].body]}]'` com filtros adequados de `--label` e `--state`.
- **Comentar issue**: `gh issue comment <number> --body "..."`.
- **Aplicar/remover labels**: `gh issue edit <number> --add-label "..."` / `--remove-label "..."`.
- **Fechar issue**: `gh issue close <number> --comment "..."`.

## Quando uma skill disser "publique no issue tracker"

Crie uma GitHub Issue.

## Quando uma skill disser "busque o ticket relevante"

Execute `gh issue view <number> --comments`.
