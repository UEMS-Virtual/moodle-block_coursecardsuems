# Contexto do domínio — block_coursecardsuems

## Estado do projeto

A implementação atual do plugin é um **protótipo visual/funcional descartável**. Ela validou direção de UX, mas não representa a arquitetura final.

A versão final deve ser reconstruída respeitando padrões Moodle. Ver:

- `docs/PROTOTYPE.md`
- `docs/adr/0001-reconstruir-plugin-apos-prototipo.md`

## Escopo vigente

O **Bloco UEMS de cursos** deve mostrar, para o aluno, as disciplinas EaD do **semestre vigente**.

Fora do escopo atual:

- cursos presenciais;
- visão específica de tutor/docente;
- histórico completo de semestres anteriores;
- substituição da visão geral nativa do Moodle;
- filtros avançados, busca, paginação ou preferências por usuário.

## Experiência desejada

O aluno deve entender rapidamente:

1. quais disciplinas estão **Abertas**;
2. quais disciplinas estão **Em breve**;
3. quais disciplinas estão **Encerradas**.

A página deve reduzir confusão causada pela mistura de disciplinas futuras, ativas e já encerradas no Moodle.

## Conceitos do domínio

| Termo | Definição |
| --- | --- |
| **Bloco UEMS de cursos** | Plugin Moodle `block_coursecardsuems`, exibido como bloco no Moodle. |
| **Disciplina EaD** | Curso Moodle de modalidade Distância que representa uma disciplina/componente curricular. |
| **Semestre vigente** | Período acadêmico corrente, exibido no título do bloco como `Semestre YYYY/S`. |
| **Componente curricular** | Nome real da disciplina, destacado como título principal do card ou item de lista. |
| **Código da disciplina** | Trecho entre colchetes no nome do curso, usado como identificador secundário; deve ter pouco destaque visual. |
| **Agrupamento compacto** | Sigla derivada do `shortname`, como `PEDG-24` ou `CISOL-23`, exibida na faixa lateral. |
| **Série** | Nível curricular imediatamente acima da disciplina na árvore de categorias, como `2ª Série`. |
| **Período informativo da disciplina** | Datas customizadas `ead_inicio` e `ead_final`, usadas para comunicar quando a disciplina acontece. |
| **Janela de acesso Moodle** | Datas nativas `course.startdate`/`course.enddate`; representam acesso ao ambiente e não devem ser exibidas como período da disciplina. |
| **Status da disciplina** | Estado temporal exibido ao aluno: **Em breve**, **Aberta** ou **Encerrada**. |
| **Tipo de oferta** | Indicação **Oferta** ou **Reoferta**, aplicável ao escopo EaD. |
| **Reoferta** | Tipo identificado pelo `shortname`, geralmente com `REO` ou `REO2`. |

## Regras de produto confirmadas

- O bloco mostra apenas disciplinas EaD do semestre vigente.
- O título do bloco deve ser `Semestre YYYY/S`, calculado dinamicamente.
- O semestre é implícito na listagem; não deve aparecer como tag em cada card.
- O agrupamento compacto, como `PEDG-24`, deve aparecer na faixa lateral.
- A série deve aparecer como tag discreta associada ao agrupamento.
- O código da disciplina deve aparecer acima do nome real da disciplina com tratamento visual secundário.
- O nome real da disciplina é a informação principal do título.
- O período exibido no card/lista deve vir de `ead_inicio` e `ead_final`.
- A janela de acesso Moodle não deve ser exibida como período da disciplina.
- A janela de acesso Moodle pode ser fallback provisório para cálculo de status quando não houver período informativo.
- A regra final de links e visibilidade está registrada em `docs/adr/0002-links-e-visibilidade-por-status.md`.

## Regras por status

### Abertas

- Seção colapsável, aberta por padrão.
- Exibição em cards com maior destaque.
- Deve permitir acesso à sala Moodle quando o curso estiver visível/disponível ao aluno.
- Se a disciplina estiver temporalmente aberta, mas oculta/indisponível no Moodle, deve ser apresentada em **Em breve** e permanecer não clicável.
- Ordenação: disciplinas que abriram mais recentemente primeiro.

### Em breve

- Seção colapsável, fechada por padrão.
- Exibição em modo lista/resumo.
- Não deve ter visual apagado.
- Deve priorizar disciplinas que abrirão primeiro.
- Não deve ser clicável.
- Disciplinas temporalmente em breve que estiverem ocultas continuam aparecendo como informação de existência da disciplina, sem link.

### Encerradas

- Seção colapsável, fechada por padrão.
- Exibição em modo lista/resumo.
- Visual apagado em tons de cinza.
- Ordenação: encerradas mais recentemente primeiro.
- Encerrada significa que o período informativo terminou; não significa necessariamente que a sala Moodle está indisponível.
- Deve continuar clicável quando o Moodle ainda permitir acesso ao aluno.
- Se estiver oculta/indisponível no Moodle, continua em **Encerradas**, mas sem link.

## Arquitetura desejada

A versão final deve separar responsabilidades:

- bloco Moodle mínimo em `block_coursecardsuems.php`;
- serviços de domínio/dados em `classes/local/`;
- renderables/view models em `classes/output/`;
- templates Mustache em `templates/`;
- strings em `lang/`;
- testes PHPUnit para regras de domínio;
- Behat para fluxos visuais importantes quando necessário.

## Questões ainda abertas

- Qual origem definitiva dos dados além dos campos `ead_inicio` e `ead_final`?
- Como a integração com cronograma externo será feita, se ainda for necessária?
- Como tratar cursos EaD que não seguem o padrão esperado de `shortname`?
- Quais capabilities específicas devem controlar visualização/configuração do bloco?
