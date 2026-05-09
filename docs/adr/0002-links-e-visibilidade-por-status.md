# ADR-0002 — Links e visibilidade por status da disciplina

## Status

Aceita.

## Contexto

O bloco organiza disciplinas EaD do semestre vigente em três status: **Abertas**, **Em breve** e **Encerradas**. O status temporal vem do Período informativo da disciplina (`ead_inicio`/`ead_final`), mas o Moodle também pode controlar disponibilidade por visibilidade/ocultação do curso.

Durante a reconstrução ficou necessário definir quando um item deve ser clicável e como tratar disciplinas ocultas que já pertencem ao semestre vigente.

## Decisão

### Em breve

Disciplinas **Em breve** não são clicáveis.

Elas devem continuar aparecendo como informação de existência da disciplina no semestre, mas sem link para a sala Moodle.

### Abertas

Disciplinas temporalmente **Abertas** são clicáveis somente quando o curso Moodle estiver visível/disponível ao aluno.

Se uma disciplina estaria **Aberta** pelas datas (`ead_inicio`/`ead_final`), mas o curso Moodle estiver oculto/indisponível porque a sala ainda não está pronta, ela deve ser apresentada na seção **Em breve** e permanecer não clicável.

### Encerradas

Disciplinas **Encerradas** continuam clicáveis quando o Moodle ainda permitir acesso ao aluno.

Se uma disciplina **Encerrada** estiver oculta/indisponível no Moodle, ela continua na seção **Encerradas**, mas sem link.

### Cursos ocultos/indisponíveis

A regra geral “não aparecem” aplica-se a cursos ocultos/indisponíveis que não sejam disciplinas EaD do semestre vigente ou que não devam compor a comunicação do semestre.

Para disciplinas EaD do semestre vigente já identificadas pelo bloco, a ocultação Moodle não remove necessariamente a disciplina da listagem. Ela altera o comportamento e, em um caso específico, o status apresentado:

- se temporalmente **Em breve** e oculta: aparece em **Em breve**, sem link;
- se temporalmente **Aberta** e oculta: aparece em **Em breve**, sem link;
- se temporalmente **Encerrada** e oculta: aparece em **Encerradas**, sem link.

## Consequências

- O status temporal bruto e o status apresentado podem divergir quando uma disciplina temporalmente aberta estiver oculta.
- A camada de view model precisa expor se o item é clicável e, quando necessário, a URL do curso.
- O template deve renderizar cards/listas como links apenas quando a regra de produto permitir.
- A disciplina oculta não deve ser tratada como erro de dados; pode representar preparação operacional da sala Moodle.
- A regra será implementada na fatia seguinte de acessibilidade/links.
