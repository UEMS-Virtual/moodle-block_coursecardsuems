# ADR-0003 — Rótulo da aba Últimas atividades e atividades remanescentes

## Status

Proposta aceita para a primeira etapa; segunda etapa em estudo.

## Contexto

O bloco classifica disciplinas EaD pelo **Período informativo** (`ead_inicio`/`ead_final`). Quando a data atual passa de `ead_final`, o status temporal interno é `closed`/`Encerrada`.

Na prática acadêmica, porém, uma disciplina cujo período informativo terminou ainda pode ter atividades relevantes na sala Moodle, como prova, exame, recuperação ou outra atividade com data futura/aberta. Portanto, o rótulo **Encerradas** na aba superior pode comunicar uma conclusão mais forte do que a regra técnica realmente garante.

## Decisão — etapa 1

Manter o status interno `closed` e as regras existentes de ordenação, layout e link.

Alterar apenas o rótulo da aba superior em português de **Encerradas** para **Últimas atividades**.

Esse rótulo remete a provas, exames, recuperação e outras atividades finais que podem existir após o período informativo principal, sem alterar o status exibido no card neste momento.

## Direção futura — etapa 2

Avaliar uma regra complementar baseada nas atividades configuradas na sala Moodle.

A ideia é calcular, por curso, se existe alguma atividade avaliável ou relevante com data futura ou ainda aberta. Esse resultado deve virar uma propriedade booleana no view model, por exemplo:

- `haspendingactivity`, ou nome equivalente definido na implementação;
- `true` quando houver atividade restante/futura/aberta;
- `false` quando não houver evidência de atividade restante.

Com essa propriedade, o card/list item poderia trocar o rótulo visual do status de **Encerrada** para uma palavra curta que indique continuidade, sem mudar necessariamente a seção ou o status temporal interno.

Possíveis nomes de interface para essa condição devem ser validados antes da implementação, por exemplo:

- **Atividade pendente**;
- **Com atividade**;
- **Ainda ativa**.

## Consequências

- A etapa 1 é uma mudança de linguagem/interface, não de regra de domínio.
- Testes de status temporal não precisam mudar, pois `closed` continua significando “após `ead_final`”.
- Documentação e validação manual devem distinguir entre:
  - status temporal interno: `Encerrada`/`closed`;
  - rótulo da aba: **Últimas atividades**;
  - possível sinalização futura de atividade remanescente.
- A etapa 2 deve ser implementada com cuidado para não tornar o bloco dependente de todos os tipos de módulo do Moodle sem uma estratégia clara de datas e relevância.