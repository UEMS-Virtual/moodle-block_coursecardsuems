# Contexto do domínio — block_coursecardsuems

## Experiência do usuário

| Termo | Definição |
|------|-----------|
| **Meus cursos** | Página Moodle `/my/courses.php` onde o usuário acessa os cursos em que está inscrito. |
| **Bloco UEMS de cursos** | Plugin Moodle `block_coursecardsuems` que exibe cursos em cards personalizados para a UEMS Virtual. |
| **Visão geral nativa** | Bloco nativo `block_myoverview` do Moodle, usado como referência técnica/conceitual, mas não como dependência obrigatória. |

## Estrutura acadêmica de categorias

| Termo | Definição |
|------|-----------|
| **Nível acadêmico** | Grande tipo de formação: **Graduação** ou **Pós-Graduação**. |
| **Modalidade** | Forma de oferta do curso: **Presencial** ou **Distância**. |
| **Unidade** | Agrupamento institucional existente nas categorias presenciais antes dos cursos. |
| **Categoria raiz UEMS** | Uma das quatro combinações principais: **Graduação/Distância**, **Graduação/Presencial**, **Pós-Graduação/Distância** e **Pós-Graduação/Presencial**. |
| **Curso acadêmico** | Curso institucional principal, como **Pedagogia**, usado como informação de destaque para cards de modalidade **Distância**. |
| **Turma acadêmica** | Agrupamento secundário de oferta, como **Turma 2024**, usado como informação complementar e menos destacada para cards de modalidade **Distância**. |
| **Série** | Nível curricular imediatamente acima das disciplinas na árvore de categorias; existe tanto em **Distância** quanto em **Presencial**. |
| **Ano da série** | Subnível existente dentro da **Série** na modalidade **Presencial**, antes das disciplinas. |
| **Período letivo** | Identificador acadêmico de oferta, como `2026/1`, exibido de forma compacta na base da faixa lateral. |
| **Janela de acesso Moodle** | Intervalo de datas nativo do Moodle usado para liberar acesso ao curso, frequentemente cobrindo o semestre inteiro. |
| **Período informativo da disciplina** | Intervalo customizado de início e fim exibido no card para comunicar quando a disciplina efetivamente acontece. |
| **Cronograma externo** | Fonte externa, atualmente planilha Google com Apps Script, usada para organizar datas informativas de disciplinas de Graduação e Pós-Graduação na modalidade **Distância**. |
| **Tipo de oferta** | Característica acadêmica da modalidade **Distância** que indica se a disciplina é **Oferta** ou **Reoferta**. |
| **Reoferta** | Tipo de oferta identificado no shortname do curso Moodle por sufixos como `REO` ou `REO2`. |
| **Status do curso** | Estado temporal da disciplina exibido de forma clara para o aluno: **Em breve**, **Aberta** ou **Encerrada**. |
| **Componente curricular** | Disciplina, sala ou ambiente Moodle específico exibido como título principal no corpo do card. |

## Decisões confirmadas

- O **Bloco UEMS de cursos** não deve substituir a **Visão geral nativa** como regra de produto.
- O plugin deve funcionar como outro bloco, complementar ao resumo/listagem padrão do Moodle.
- O escopo inicial do bloco deve focar nas disciplinas do semestre e na comunicação clara do estado de cada disciplina para o aluno.
- Mudança de escopo confirmada: o bloco deve abordar apenas disciplinas **EaD** do **semestre vigente**.
- Como a listagem passa a representar somente o **semestre vigente**, a tag de **Período letivo** deixa de ser necessária no card por ser redundante.
- O espaço visual antes usado pela tag de **Período letivo** pode ser reaproveitado para exibir a **Série**.
- Na faixa lateral, o card deve exibir a **Turma acadêmica**.
- O plugin não deve depender de uma alteração irreversível na instalação Moodle para cumprir sua função principal.

## Fatos confirmados

- A árvore de categorias acadêmicas da UEMS possui quatro combinações principais: **Graduação/Distância**, **Graduação/Presencial**, **Pós-Graduação/Distância** e **Pós-Graduação/Presencial**.
- Categorias de modalidade **Presencial** possuem separação por **Unidade** antes dos cursos.
- Categorias de modalidade **Distância** não possuem separação por **Unidade** antes dos cursos.
- Na modalidade **Distância**, existe **Série** como último nível de categoria antes das disciplinas.
- Na modalidade **Presencial**, existe **Série** e, dentro dela, **Ano da série** antes das disciplinas.
- Para modalidade **Distância**, no escopo anterior, o card destacava o **Curso acadêmico** como informação principal do agrupamento visual e exibia a **Turma acadêmica** como informação secundária/desfocada.
- Regra vigente de escopo: como o bloco deve exibir apenas disciplinas **EaD** do **semestre vigente**, a **Faixa lateral** deve exibir a **Turma acadêmica**.
- A **Série** deve ocupar o espaço da antiga tag de **Período letivo**, já que o semestre vigente estará implícito na própria listagem.
- Regras específicas para modalidade **Presencial** ficam fora do escopo atual do plugin.
- O **Ano da série**, quando existir, também pode aparecer como metadado secundário discreto/desfocado.
- O título principal no corpo do card deve ser o **Componente curricular**, derivado do nome específico do curso Moodle, evitando repetir o **Curso acadêmico** já exibido na faixa lateral.
- O **Período letivo** pode continuar existindo como dado de domínio, mas não deve ser exibido no card enquanto o bloco estiver limitado ao **semestre vigente**.
- O **Período letivo** não deve ser confundido com **Turma acadêmica**, **Série** ou **Ano da série**.
- O plugin deve distinguir a **Janela de acesso Moodle** do **Período informativo da disciplina**.
- A **Janela de acesso Moodle** pode cobrir o semestre inteiro para permitir acesso contínuo a materiais e avaliações, mesmo quando a disciplina já aconteceu.
- O **Período informativo da disciplina** deve ser exibido no card como a data de início/fim que ajuda o aluno a entender quando a disciplina efetivamente ocorre.
- Para Graduação e Pós-Graduação na modalidade **Distância**, o **Período informativo da disciplina** deve vir futuramente do **Cronograma externo** mantido em planilha Google/Apps Script.
- Quando existir **Período informativo da disciplina**, o card deve exibir apenas esse período na linha de datas principal.
- Quando não existir **Período informativo da disciplina**, a linha de datas principal do card deve ficar oculta; a **Janela de acesso Moodle** não deve ser exibida como se fosse data da disciplina.
- A **Janela de acesso Moodle** não deve aparecer no card principal, para evitar confusão com o período real da disciplina.
- Mesmo quando a linha de datas estiver oculta por falta de **Período informativo da disciplina**, o **Status do curso** pode usar a **Janela de acesso Moodle** como fallback para determinar **Em breve**, **Aberta** ou **Encerrada**.
- Quando o **Status do curso** for calculado por fallback da **Janela de acesso Moodle**, ele deve continuar exibindo **Em breve**, **Aberta** ou **Encerrada** normalmente, mas sem linha de data no card.
- A ordenação das **Abertas** deve priorizar a disciplina que abriu mais recentemente.
- A ordenação das **Em breve** deve priorizar a disciplina que abrirá primeiro.
- Mensagens de estado vazio devem considerar que podem existir disciplinas ocultas para o aluno; portanto, não devem afirmar de forma absoluta que não há disciplinas vinculadas à matrícula.

## Regras de acesso e apresentação por status

- Disciplinas **Em breve** devem aparecer apenas como informação textual, sem link para a sala Moodle.
- Disciplinas **Abertas** devem permitir acesso à sala Moodle.
- Disciplinas **Encerradas** precisam ser tratadas conforme a razão do encerramento:
  - se estão encerradas apenas pelo **Período informativo da disciplina**, mas a sala Moodle ainda está acessível, podem manter link para a sala;
  - se estão ocultas ou efetivamente indisponíveis no Moodle, devem aparecer apenas como informação, sem link.
- A exibição deve separar visualmente as disciplinas entre os três estados: **Em breve**, **Aberta** e **Encerrada**.

## Ideias futuras fora do escopo atual

- Criar uma aba ou alternância superior por papel do usuário, pois **Aluno**, **Tutor** e **Docente** podem precisar de visualizações diferentes.
- A primeira fase deve manter o escopo apenas na visão do **Aluno**.

## Regras vigentes de escopo visual

- Mostrar apenas disciplinas **EaD** do **semestre vigente**.
- Não exibir tag de **Período letivo** no card, pois o semestre vigente é implícito na listagem.
- Usar a tag inferior da faixa lateral para exibir a **Série**.
- Usar a faixa lateral para exibir a **Turma acadêmica**.
- Tratar regras de cursos **Presenciais** como fora do escopo atual.

## Problemas em aberto

- O termo **Encerrada** é ambíguo: pode significar que o período de aulas da disciplina terminou, mas não significa necessariamente que a sala Moodle deixou de estar acessível.
- Disciplinas cujo **Período informativo da disciplina** terminou ainda podem exigir estudo, revisão, prova ou acesso até o fim do semestre.
- Antes de fechar o layout final de disciplinas encerradas, o projeto precisa definir como comunicar claramente a diferença entre disciplina pedagogicamente encerrada e sala Moodle indisponível.
- **Oferta** e **Reoferta** são características da modalidade **Distância**.
- A **Reoferta** pode ser identificada no shortname do curso Moodle por sufixos como `REO` ou `REO2`, por exemplo `PEDG_24_2S_D_(REO)_d74cd`.
- Quando o shortname não indicar `REO`/`REO2` em curso de modalidade **Distância**, o **Tipo de oferta** pode ser tratado como **Oferta**.
- O badge de **Tipo de oferta** deve aparecer apenas para cursos da modalidade **Distância**.
- Em cursos da modalidade **Presencial**, o badge **Oferta/Reoferta** deve ser ocultado, pois dependência/adaptação pode ocorrer cursando disciplina em outra turma dentro da própria oferta regular.
- A principal regra de ouro do plugin é deixar claro para o aluno quais disciplinas do semestre ainda vão começar, quais estão abertas e quais já encerraram.
- O **Status do curso** deve reduzir confusão causada por filtros e pela dificuldade dos alunos em diferenciar disciplinas futuras, abertas e encerradas.
- O escopo atual considera apenas a visão do **Aluno**. Visões específicas para **Tutor** e **Docente** são ideias futuras.
- O **Status do curso** exibido no card deve ser calculado pelo **Período informativo da disciplina** quando ele existir.
- Quando não houver **Período informativo da disciplina**, o **Status do curso** pode usar a **Janela de acesso Moodle** como fallback provisório.
- A **Janela de acesso Moodle** não deve determinar o status principal quando houver cronograma customizado, porque representa acesso ao ambiente, não necessariamente ocorrência da disciplina.
