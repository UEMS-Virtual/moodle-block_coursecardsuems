# Domain Docs

Como as engineering skills devem consumir a documentação de domínio deste repo.

## Layout

Este repo usa **single-context**:

```text
/
├── CONTEXT.md
└── docs/adr/
```

- `CONTEXT.md` fica na raiz do repo.
- ADRs arquiteturais devem ficar em `docs/adr/` quando existirem.
- `CONTEXT-MAP.md` não é usado neste repo no momento.

## Antes de explorar ou implementar

Leia:

1. `CONTEXT.md` na raiz, se existir.
2. ADRs relevantes em `docs/adr/`, se existirem.
3. Documentos próximos ao tema em `docs/`, quando a tarefa envolver produto, design ou arquitetura.

Se algum arquivo ou diretório não existir, prossiga silenciosamente. Não sugira criá-los upfront; crie ou atualize docs apenas quando a tarefa pedir ou quando uma decisão tiver sido confirmada.

## Atenção: contexto atual parcialmente depreciado

O `CONTEXT.md` atual nasceu durante um protótipo visual/funcional do plugin e contém decisões históricas, algumas já superadas.

Decisão vigente importante:

- A implementação atual deve ser tratada como **protótipo descartável/validatório**.
- A versão final do plugin deve ser reconstruída respeitando padrões Moodle, separando domínio, dados, renderização e templates.
- Antes de implementar mudanças estruturais, confirme se o `CONTEXT.md` e o `README.md` já foram reescritos para o novo escopo.

## Use o vocabulário do domínio

Quando sua saída nomear um conceito de domínio — título de issue, proposta de refatoração, hipótese de diagnóstico, nome de teste — use os termos definidos no `CONTEXT.md` vigente.

Se o conceito necessário não existir no glossário, isso é um sinal: talvez você esteja inventando linguagem fora do domínio, ou há uma lacuna real que precisa ser documentada.

## Conflitos com ADRs

Se sua proposta contradisser uma ADR existente, sinalize explicitamente em vez de sobrescrever a decisão silenciosamente:

> Contradiz ADR-0001 — vale reabrir porque...
