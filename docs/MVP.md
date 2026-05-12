> **Documento histórico** — planejamento do MVP inicial. O plugin foi reconstruído e está em produção; consulte `README.md` e `CONTEXT.md` para o estado atual.

# MVP do plugin

## Objetivo

Criar uma primeira versão funcional do bloco `block_coursecardsuems` para validar o comportamento dentro do Moodle antes de aprofundar decisões de design, campos personalizados e integrações.

## Escopo implementado

- Plugin Moodle do tipo bloco instalável.
- Bloco adicionável ao Dashboard e demais páginas permitidas pelo Moodle.
- Listagem dos cursos em que o usuário logado está inscrito.
- Card inteiro clicável, apontando para a página do curso.
- Layout sem banner/capa, seguindo o padrão 02 definido em `docs/DESIGN.md`.
- Faixa lateral usando a cor principal herdada do tema/site.
- Rótulo vertical provisório usando a categoria imediata do curso.
- Período/semestre calculado provisoriamente pela data de início do curso.
- Status calculado pelas datas do curso:
  - `Em breve`, quando a data de início ainda não chegou;
  - `Encerrada`, quando a data de término já passou;
  - `Aberta`, nos demais casos.
- Exibição de docentes prováveis com base em usuários inscritos com capacidade de gerenciar atividades no curso.
- Suporte inicial a idioma inglês e português do Brasil.

## Decisões provisórias do MVP

Estas decisões existem apenas para permitir teste funcional no Moodle:

- O rótulo vertical usa a categoria imediata do curso, mas isso ainda precisa ser revisto pela equipe devido à árvore de categorias complexa da UEMS.
- O período/semestre é calculado pela data de início, mas futuramente pode vir de campo personalizado ou integração com o sistema acadêmico.
- A cor da faixa lateral não varia por categoria nesta versão; usa a cor principal do tema.
- O tipo de oferta aparece como `Oferta` fixo nesta versão.

## Como testar

1. Acessar o Moodle como administrador.
2. Executar a atualização do banco quando o Moodle detectar o novo plugin.
3. Ativar edição no Dashboard ou página desejada.
4. Adicionar o bloco `Cards de cursos UEMS`.
5. Acessar com um usuário inscrito em cursos.
6. Verificar se os cards aparecem e se o clique abre o curso correspondente.

## Fora do escopo do MVP

- Configurações administrativas do bloco.
- Campos personalizados da UEMS.
- Integração com sistema acadêmico.
- Busca, filtros, paginação ou AJAX.
- Variação de cor por categoria.
- Definição final do nível de categoria a exibir.
- Definição final do cálculo/origem do semestre.
