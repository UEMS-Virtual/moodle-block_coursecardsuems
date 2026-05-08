# Configuração do repositório

## Organização GitHub

O projeto deve ficar na organização:

<https://github.com/UEMS-Virtual>

## Nome do repositório

Nome a confirmar antes da criação no GitHub.

Sugestões compatíveis com o padrão Moodle:

1. `moodle-block_coursecardsuems`
2. `block_coursecardsuems`
3. `coursecardsuems`

Sugestão preferencial: `moodle-block_coursecardsuems`, por deixar explícito que é um plugin Moodle do tipo bloco.

## Diretório local

O plugin está sendo planejado em:

`blocks/coursecardsuems`

## Atenção sobre Git

O diretório atual está dentro de uma instalação/repositório Moodle maior. Portanto, a configuração do repositório próprio do plugin deve ser feita com cuidado para não alterar o remoto do Moodle principal.

Antes de configurar o remoto definitivo, confirmar:

- nome final do repositório;
- se o repositório já existe na organização UEMS Virtual;
- se será usado HTTPS ou SSH;
- se este diretório será versionado como repositório Git independente.

## Comandos previstos após confirmação

Exemplo usando o nome sugerido:

```bash
git init
git branch -M main
git remote add origin https://github.com/UEMS-Virtual/moodle-block_coursecardsuems.git
```

Se o repositório já existir e o remoto precisar ser ajustado:

```bash
git remote set-url origin https://github.com/UEMS-Virtual/moodle-block_coursecardsuems.git
```

> Não executar estes comandos sem confirmação, pois o diretório está dentro do repositório Moodle principal.
