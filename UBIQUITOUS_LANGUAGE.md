# Ubiquitous Language — block_coursecardsuems

Use estes termos em documentação, issues e código de interface.

## Experiência do usuário

| Termo | Definição | Evitar |
|---|---|---|
| **Bloco UEMS de cursos** | Plugin `block_coursecardsuems` exibido como bloco no Moodle. | bloco nativo, my overview |
| **Disciplina EaD** | Curso Moodle de modalidade Distância que representa um componente curricular. | disciplina do aluno, sala |
| **Semestre vigente** | Período acadêmico corrente, exibido no cabeçalho do bloco como `Semestre YYYY/S`. | período letivo, ano letivo |
| **Aba de status** | Cada aba (Abertas / Em breve / Encerradas) que agrupa disciplinas. | seção colapsável, accordion |
| **Estado vazio** | Mensagem exibida quando não há disciplinas em uma aba. | tela vazia, sem dados |

## Dados da disciplina

| Termo | Definição | Evitar |
|---|---|---|
| **Componente curricular** | Nome real da disciplina, título principal do card ou item de lista. | nome do curso, fullname |
| **Código da disciplina** | Prefixo entre colchetes no `fullname`; removido do display final. | shortname, sigla |
| **Agrupamento compacto** | Sigla derivada do `shortname`, ex: `PEDG-24`. Aparece no supertítulo. | categoria, período letivo |
| **Série** | Nível curricular acima da disciplina na árvore de categorias, ex: `2ª Série`. | rótulo vertical, categoria |
| **Supertítulo** | Linha acima do nome da disciplina; composta por `agrupamento · série`. | cabeçalho, tag lateral |
| **Período informativo** | Datas `ead_inicio` e `ead_final` dos campos customizados; comunicam quando a disciplina acontece. | data de início/término, datas Moodle |
| **Janela de acesso Moodle** | `course.startdate`/`course.enddate`; usados só como fallback de status. Nunca exibidos ao aluno. | período da disciplina |
| **Status da disciplina** | Estado temporal: **Em breve**, **Aberta** ou **Encerrada**. | situação, visibilidade |
| **Tipo de oferta** | **Oferta** ou **Reoferta**; classificação da oferta acadêmica. | badge, status |
| **Reoferta** | Oferta identificada pelo `shortname` (presença de `REO`). Exibida como ribbon no card. | segunda oferta, repetição |
| **Docente** | Usuário com papel `editingteacher` ou `teacher` exibido no card. | professor, tutor |

## Design do card

| Termo | Definição | Evitar |
|---|---|---|
| **Card de disciplina** | Componente visual (grid) que representa uma disciplina Aberta. | tile, box, cartão |
| **Item de lista** | Linha compacta que representa disciplina Em breve ou Encerrada. | card de lista, resumo |
| **Ribbon** | Marcador colorido no canto do card que exibe Reoferta ou status. | badge, faixa status, etiqueta |
| **Avatar de docente** | Círculo com iniciais do docente exibido no card. | foto, bolinha |

## Relacionamentos

- Um **Bloco UEMS de cursos** contém três **Abas de status**.
- Cada aba exibe zero ou mais **Disciplinas EaD** como **Cards de disciplina** (Abertas) ou **Itens de lista** (Em breve / Encerradas).
- O **Tipo de oferta** (`Oferta`/`Reoferta`) e o **Status da disciplina** (`Aberta`/`Em breve`/`Encerrada`) são conceitos independentes.
- O **Supertítulo** combina **Agrupamento compacto** e **Série**.
