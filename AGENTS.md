# AGENTS.md

## Agent skills

### Issue tracker

Issues e PRDs são rastreados no GitHub Issues. Neste WSL, preferir `curl` com `$(gh auth token)` para evitar falhas intermitentes de DNS do `gh`; ver detalhes em `docs/agents/issue-tracker.md`.

### Triage labels

Labels de triagem usam vocabulário em português. See `docs/agents/triage-labels.md`.

### Domain docs

Layout single-context: `CONTEXT.md` na raiz e ADRs em `docs/adr/`. Atenção: o `CONTEXT.md` atual contém decisões de protótipo e está parcialmente depreciado; confirme o escopo vigente antes de implementar. See `docs/agents/domain.md`.
