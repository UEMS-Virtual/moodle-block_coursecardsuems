# Ubiquitous Language

Este glossário define a linguagem comum do projeto `block_coursecardsuems`. Use estes termos em documentação, PRDs, issues, código de interface e conversas de produto.

## Experiência do usuário

| Term                     | Definition                                                                                          | Aliases to avoid                                      |
| ------------------------ | --------------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| **Meus cursos**          | Página Moodle onde o usuário acessa os cursos em que está inscrito.                                 | Painel, Dashboard, página inicial                     |
| **Resumo dos cursos**    | Título funcional da área/bloco que apresenta os cursos do usuário em formato de cards.              | Cards de cursos UEMS, visão geral, course overview    |
| **Bloco UEMS de cursos** | Plugin Moodle `block_coursecardsuems` que exibe cursos em cards personalizados para a UEMS Virtual. | My overview, bloco nativo, lista de cursos            |
| **Card de curso**        | Componente visual clicável que representa um curso matriculado do usuário.                          | Cartão, tile, item, box                               |
| **Curso matriculado**    | Curso em que o usuário logado possui matrícula ativa ou visível para listagem.                      | Curso disponível, curso ofertado, disciplina do aluno |
| **Estado vazio**         | Mensagem exibida quando não há cursos para mostrar ao usuário.                                      | Tela vazia, sem dados, nenhum resultado               |

## Dados acadêmicos exibidos no card

| Term                | Definition                                                                                                    | Aliases to avoid                                 |
| ------------------- | ------------------------------------------------------------------------------------------------------------- | ------------------------------------------------ |
| **Nome do curso**   | Nome exibido como informação principal do card, atualmente derivado do curso Moodle.                          | Nome da disciplina, título, fullname             |
| **Docente**         | Pessoa vinculada ao curso com papel/capacidade docente exibida no card.                                       | Professor, tutor, instrutor                      |
| **Período letivo**  | Identificador curto do período acadêmico, como `2025/1`, exibido na faixa lateral.                            | Semestre, período, ano/semestre                  |
| **Data de início**  | Data em que o curso começa segundo o cadastro do curso Moodle.                                                | Início, começo, abertura                         |
| **Data de término** | Data em que o curso termina segundo o cadastro do curso Moodle.                                               | Fim, encerramento, fechamento                    |
| **Tipo de oferta**  | Classificação acadêmica da oferta do curso, inicialmente representada por `Oferta` ou futuramente `Reoferta`. | Badge oferta, situação, status                   |
| **Status do curso** | Estado temporal/operacional exibido no card, como `Aberta`, `Em breve` ou `Encerrada`.                        | Tipo de oferta, situação acadêmica, visibilidade |
| **Código curto**    | Código institucional ou acadêmico opcional a ser exibido junto ao docente ou metadados do curso.              | Shortname, sigla, código da disciplina           |

## Design do card

| Term                   | Definition                                                                                                      | Aliases to avoid                            |
| ---------------------- | --------------------------------------------------------------------------------------------------------------- | ------------------------------------------- |
| **Faixa lateral**      | Área vertical colorida à esquerda do card usada para exibir agrupamento e período letivo.                       | Barra lateral, tarja, lateral azul          |
| **Rótulo vertical**    | Texto rotacionado exibido dentro da faixa lateral para representar série, área, categoria ou outro agrupamento. | Categoria, série, label lateral             |
| **Badge**              | Pequeno marcador textual usado para destacar tipo de oferta ou outro metadado curto.                            | Tag, etiqueta, pílula                       |
| **Ribbon de status**   | Marcador visual no canto superior direito do card usado para exibir o status do curso.                          | Badge aberta, faixa status, etiqueta status |
| **Avatar de docente**  | Círculo com iniciais usado para representar visualmente o docente.                                              | Foto, bolinha, iniciais                     |
| **Linha de metadados** | Área inferior do card onde aparecem tipo de oferta, datas e outros metadados secundários.                       | Rodapé do card, footer, base                |
| **Padrão 02**          | Direção visual escolhida para cards sem capa/banner e com faixa lateral por categoria/agrupamento.              | Design 02, layout sem capa, mockup 02       |

## Fontes de dados e integração

| Term                             | Definition                                                                                           | Aliases to avoid                         |
| -------------------------------- | ---------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| **Curso Moodle**                 | Registro nativo de curso no Moodle usado como fonte atual para nome, datas, categoria e matrícula.   | Disciplina Moodle, sala, turma           |
| **Categoria Moodle**             | Categoria nativa do Moodle usada provisoriamente para o rótulo vertical no MVP.                      | UU, série, agrupamento acadêmico         |
| **Campo personalizado de curso** | Campo configurável no Moodle que pode armazenar metadados acadêmicos específicos da UEMS.            | Custom field, campo extra, metadado      |
| **Argos**                        | Sistema/fonte externa de dados acadêmicos usado para importar categorias, cursos, docentes e alunos. | JSON, integração, sistema acadêmico      |
| **Dados acadêmicos externos**    | Informações vindas do Argos ou outro sistema institucional, não digitadas manualmente no Moodle.     | Dados da integração, payload, importação |

## Relacionamentos

- Um **Bloco UEMS de cursos** exibe zero ou mais **Cards de curso**.
- Um **Card de curso** representa exatamente um **Curso matriculado** para o usuário logado.
- Um **Curso matriculado** deriva de um **Curso Moodle**, mas pode futuramente receber **Dados acadêmicos externos**.
- Um **Card de curso** pode exibir um ou mais **Docentes**.
- A **Faixa lateral** contém um **Rótulo vertical** e um **Período letivo**.
- O **Tipo de oferta** e o **Status do curso** são conceitos diferentes: `Oferta/Reoferta` classifica a oferta acadêmica; `Aberta/Em breve/Encerrada` indica o estado temporal/operacional.
- A **Categoria Moodle** é apenas a origem provisória do **Rótulo vertical** no MVP; a origem final ainda está em aberto.

## Example dialogue

> **Dev:** Quando o usuário entra em **Meus cursos**, ele deve ver o **Resumo dos cursos**?
>
> **Domain expert:** Sim. O **Bloco UEMS de cursos** deve substituir a visão padrão nessa página.
>
> **Dev:** Cada **Card de curso** representa um **Curso Moodle** em que o usuário está matriculado?
>
> **Domain expert:** Sim, mas alguns metadados como **Tipo de oferta**, **Período letivo** e talvez **Rótulo vertical** podem vir de **Dados acadêmicos externos** no futuro.
>
> **Dev:** Então `Oferta` não é o mesmo que `Aberta`?
>
> **Domain expert:** Exato. **Tipo de oferta** diz se é **Oferta** ou **Reoferta**. **Status do curso** diz se está **Aberta**, **Em breve** ou **Encerrada**.

## Flagged ambiguities

- "Curso" e "disciplina" aparecem próximos. Recomendação atual: usar **Curso Moodle** para o registro técnico do Moodle e **Nome do curso** para o texto principal exibido no card. Se a UEMS quiser distinguir curso, disciplina e turma academicamente, isso precisa ser resolvido no grill.
- "Oferta" estava sendo usado como badge visual e como possível situação. Recomendação: usar **Tipo de oferta** para `Oferta/Reoferta` e **Status do curso** para `Aberta/Em breve/Encerrada`.
- "Faixa lateral" já exibiu série/categoria. Recomendação: usar **Rótulo vertical** enquanto a origem final não for decidida.
- "Meus cursos", "Dashboard" e "Página inicial" podem ser confundidos no Moodle. Recomendação: **Meus cursos** significa `/my/courses.php`; **Dashboard/Painel** significa `/my/index.php`; **Página inicial** significa frontpage do site.
- "Docente" e "professor" são sinônimos no uso cotidiano. Recomendação: usar **Docente** na interface e nos requisitos do plugin.
