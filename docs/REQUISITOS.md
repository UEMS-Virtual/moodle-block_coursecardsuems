> **Documento histórico** — levantamento inicial de requisitos. Requisitos vigentes estão em `CONTEXT.md` e nos ADRs.

# Requisitos — block_coursecardsuems

## Contexto

Este repositório será um plugin Moodle do tipo **bloco** para a UEMS Virtual.

Nome técnico inicial:

- Diretório Moodle: `blocks/coursecardsuems`
- Componente Moodle: `block_coursecardsuems`
- Organização GitHub: <https://github.com/UEMS-Virtual>

## Diretriz inicial do projeto

Antes de qualquer implementação, o projeto deve produzir e validar documentação de requisitos.

Nesta etapa, **não serão criados arquivos de código do plugin**. São permitidos apenas arquivos de documentação, como requisitos, decisões arquiteturais, escopo e planejamento.

## Objetivo do plugin

Criar um bloco Moodle para exibir cursos em formato de cards personalizados para a UEMS, com uma experiência visual e funcional adequada ao ambiente da universidade.

O plugin será independente, ainda que possa usar o bloco nativo de visão geral de cursos do Moodle como referência conceitual e técnica.

## Escopo inicial

### Incluído

- Plugin Moodle do tipo bloco.
- Exibição de cards de cursos.
- Uso exclusivo/primário no ambiente UEMS Virtual.
- Definição prévia dos requisitos antes da implementação.
- Planejamento de comportamento, dados exibidos e regras de visibilidade.

### Fora de escopo nesta etapa

- Criação da estrutura PHP do plugin.
- Criação de classes, templates Mustache, JavaScript, CSS ou arquivos de idioma.
- Implementação de consultas Moodle.
- Implementação de AJAX, filtros, paginação ou preferências de usuário.
- Publicação no diretório oficial de plugins do Moodle.

## Requisitos funcionais a definir

1. Onde o bloco poderá ser usado:
   - Dashboard;
   - Meus cursos;
   - páginas de curso;
   - outras páginas Moodle.

2. Quais cursos serão exibidos:
   - somente cursos em que o usuário está inscrito;
   - cursos disponíveis para inscrição;
   - cursos favoritos;
   - cursos em andamento;
   - cursos futuros;
   - cursos encerrados;
   - cursos ocultos.

3. Quais informações aparecerão em cada card:
   - nome do curso;
   - categoria ou agrupamento visual;
   - faixa lateral com cor herdada do tema/site;
   - categoria, área ou agrupamento visual exibido na faixa lateral;
   - período/semestre;
   - tipo de oferta, como oferta ou reoferta;
   - status, como aberta, em breve ou encerrada;
   - docentes, com avatar/iniciais;
   - datas de início e término;
   - código curto do curso, se necessário;
   - outros campos personalizados da UEMS.

   Decisão inicial: a primeira versão não usará imagem/banner/capa no card.

4. Origem dos dados exibidos:
   - campos nativos do curso Moodle;
   - campos personalizados de curso;
   - configuração local do bloco;
   - futuramente, dados vindos de integração com sistema acadêmico, se disponível.

   Decisão inicial: imagem padrão do curso, campo personalizado de banner e mapeamento de cores por categoria não são necessários para o padrão visual escolhido.

5. Comportamentos esperados:
   - card inteiro clicável para abrir o curso;
   - ordenação dos cursos;
   - limite de cards exibidos;
   - mensagem para usuário sem cursos;
   - responsividade;
   - acessibilidade básica.

## Requisitos não funcionais iniciais

- Compatibilidade com Moodle 4.5.x.
- Código futuro deve seguir padrões de plugin Moodle.
- Evitar acoplamento direto com `block_myoverview`.
- Preferir renderização server-side na primeira versão, salvo decisão posterior.
- Manter o escopo inicial simples e validável.
- Documentar decisões antes da implementação.

## Decisões arquiteturais iniciais

- O plugin será novo e independente.
- O componente Moodle será `block_coursecardsuems`.
- O bloco nativo `block_myoverview` poderá ser estudado como referência, mas não deve ser herdado diretamente sem justificativa.
- A primeira fase deve priorizar clareza dos requisitos e baixa complexidade.
- O padrão visual inicial escolhido é o padrão 02: card sem capa/banner, com faixa lateral, documentado em `docs/DESIGN.md`.
- A cor da faixa lateral deve herdar inicialmente a cor principal do tema/site.
- Mapeamento de cores por categoria, campo personalizado ou configuração é melhoria futura a ser discutida com a equipe.
- O rótulo vertical da faixa lateral teoricamente representa categoria/agrupamento, mas a origem ainda precisa ser definida devido à complexidade da árvore de categorias da UEMS.
- O período/semestre pode ser calculado pelas datas de início/fim ou preenchido por dados do sistema acadêmico.

## Perguntas em aberto

1. Qual será o nome final do repositório no GitHub da UEMS Virtual?
2. O bloco será usado apenas no Dashboard ou também em outras páginas?
3. O bloco deve mostrar somente cursos inscritos do usuário?
4. A descrição curta será removida da primeira versão ou aparecerá em algum estado expandido?
5. Quais campos personalizados são obrigatórios para a UEMS?
6. Qual variável/cor do tema Moodle será usada como cor principal da faixa lateral?
7. Qual será a origem do rótulo vertical: árvore de categorias, campo personalizado ou outro dado institucional?
8. Se for árvore de categorias, qual nível deve ser considerado?
9. O período/semestre será calculado pelas datas ou informado por integração/campo externo?
10. Haverá diferença visual por categoria, modalidade ou status?
11. Cursos ocultos, futuros ou encerrados devem aparecer?
12. O usuário poderá ocultar/favoritar cursos neste bloco?
13. A primeira versão precisa ter busca, filtros ou paginação?

## Próximo passo sugerido

Validar este documento com os responsáveis pelo projeto e transformar as respostas das perguntas em aberto em decisões formais antes de iniciar qualquer implementação.
