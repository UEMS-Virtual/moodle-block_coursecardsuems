# Issue tracker: GitHub

Issues e PRDs deste repo vivem como GitHub Issues. Use o CLI `gh` para operações no tracker.

## Convenções

- **Criar issue**: `gh issue create --title "..." --body "..."`. Use heredoc para corpos multilinha.
- **Ler issue**: `gh issue view <number> --comments`, incluindo comentários e labels.
- **Listar issues**: `gh issue list --state open --json number,title,body,labels,comments --jq '[.[] | {number, title, body, labels: [.labels[].name], comments: [.comments[].body]}]'` com filtros adequados de `--label` e `--state`.
- **Comentar issue**: `gh issue comment <number> --body "..."`.
- **Aplicar/remover labels**: `gh issue edit <number> --add-label "..."` / `--remove-label "..."`.
- **Fechar issue**: `gh issue close <number> --comment "..."`.

O repo deve ser inferido pelo diretório atual e pelo `git remote -v`; o `gh` normalmente faz isso automaticamente dentro do clone.

## Quando uma skill disser "publique no issue tracker"

Crie uma GitHub Issue.

## Quando uma skill disser "busque o ticket relevante"

Execute `gh issue view <number> --comments`.
