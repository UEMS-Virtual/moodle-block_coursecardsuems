# Contexto do domínio — block_coursecardsuems

## Escopo

O bloco exibe, para o aluno, as disciplinas EaD do **semestre vigente** organizadas em três abas: **Abertas**, **Em breve**, **Últimas atividades**.

Fora do escopo: cursos presenciais, visão de tutor/docente, histórico de semestres, filtros avançados.

## Público e permissões

O bloco é uma visão de aluno. Usuários comuns só veem cursos em que tenham a capability `block/coursecardsuems:viewcontent` no contexto do curso, concedida por padrão ao papel `student`; managers não passam por permissões amplas (`doanything`) para este filtro.

Administradores do site são exceção operacional: veem todas as disciplinas EaD do semestre vigente para inspeção e suporte, inclusive cursos ocultos e sem matrícula. A classificação visual permanece igual à do aluno; a diferença é que links são permitidos para o admin. Para auditoria de cadastro, admins também podem ver disciplinas EaD reconhecidas sem `ead_inicio`/`ead_final`, marcadas com aviso de período informativo ausente.

## Conceitos do domínio

| Termo | Definição |
|---|---|
| **Disciplina EaD** | Curso Moodle de modalidade Distância que representa um componente curricular. |
| **Semestre vigente** | Período acadêmico corrente, exibido no cabeçalho como `Semestre YYYY/S`. |
| **Componente curricular** | Nome real da disciplina (título principal do card ou item de lista). O prefixo entre colchetes é removido do display. |
| **Agrupamento compacto** | Sigla derivada do `shortname`, ex: `PEDG-24`. Aparece no supertítulo. |
| **Série** | Nível curricular acima da disciplina na árvore de categorias, ex: `2ª Série`. Aparece no supertítulo ao lado do agrupamento. |
| **Período informativo** | Datas `ead_inicio` e `ead_final` dos campos customizados; comunicam quando a disciplina acontece. |
| **Janela de acesso Moodle** | `course.startdate`/`course.enddate`; usados apenas como fallback de status quando o período informativo estiver ausente. Nunca exibidos como período da disciplina. |
| **Status da disciplina** | Estado temporal: **Em breve**, **Aberta** ou **Encerrada**. |
| **Reoferta** | Oferta identificada pelo `shortname` (presença de `REO`, incluindo `REO2`). Exibida como ribbon no card. |
| **Mapa de cores por curso/turma** | Configuração JSON do plugin que associa siglas normalizadas (`PEDG24`, `PEDG24-REO`) a cores hexadecimais para a faixa do card. Sem correspondência válida, usa fallback azul. |
| **Aba de status** | Cada aba (Abertas / Em breve / Últimas atividades) agrupa as disciplinas por status e controla o layout. |

## Regras por status

### Abertas
- Exibição em cards (grid), aba ativa por padrão.
- Clicáveis quando o curso Moodle estiver visível/disponível.
- Disciplina temporalmente aberta mas oculta/indisponível aparece em **Em breve**, sem link.

### Em breve
- Exibição em lista.
- Nunca clicáveis.
- Prioridade: disciplinas que abrem primeiro.

### Últimas atividades
- Corresponde ao status temporal interno **Encerrada** (`closed`): o período informativo terminou, mas isso não garante que todas as atividades da sala Moodle terminaram.
- Exibição em lista, tom apagado.
- Clicáveis quando o Moodle ainda permitir acesso.
- Se oculta/indisponível: sem link, mas permanece na seção Últimas atividades.
- Possível evolução futura: sinalizar no item quando houver atividade futura/aberta na sala Moodle. Ver `docs/adr/0003-rotulo-aba-apos-periodo-e-atividades-remanescentes.md`.

Regra completa: `docs/adr/0002-links-e-visibilidade-por-status.md`.

## Arquitetura vigente

- **Abas AMD**: `amd/src/section_tabs.js` gerencia a alternância; o Mustache renderiza todos os painéis e oculta os inativos com `hidden`.
- **View model**: `course_card_mapper.php` produz o array de cada disciplina; `summary.php` agrupa em seções.
- **Templates**: `summary.mustache` (abas + painéis) e `card.mustache` (card individual).
- **Supertítulo**: `agrupamento · série`, derivado de `shortname` e categoria Moodle.
- **Código da disciplina**: removido do título exibido; nunca renderizado no card ou na lista.

## Issues de referência

| Issue | Assunto |
|---|---|
| #20 | Adicionar contagem de Agendas (próxima a implementar) |
| #22 | Migração de `<details>` colapsável para abas AMD (concluída) |
| #17/#18 | Design aprovado: ribbon, supertítulo, remoção do código em destaque (concluído) |
