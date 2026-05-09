# Protótipo visual/funcional

Este documento registra o papel da implementação atual de `block_coursecardsuems`.

## Decisão

O código atual é um **protótipo visual/funcional descartável**.

Ele foi útil para validar rapidamente:

- direção visual dos cards;
- seções por status: **Abertas**, **Em breve** e **Encerradas**;
- cards grandes para disciplinas abertas;
- listas compactas para disciplinas em breve e encerradas;
- uso de datas customizadas `ead_inicio` e `ead_final`;
- uso de faixa lateral, série, código do curso e docentes;
- comportamento visual de disciplinas encerradas em tom apagado.

Ele **não** deve ser tratado como arquitetura final.

## Problemas arquiteturais conhecidos

A implementação atual concentra responsabilidades em `block_coursecardsuems.php`, incluindo:

- acesso a dados;
- regra de domínio;
- parsing de categoria e shortname;
- cálculo de status;
- cálculo de semestre vigente;
- montagem manual de HTML;
- decisões visuais.

Isso contraria boas práticas Moodle para plugins mantíveis.

## O que pode ser reaproveitado

Pode ser usado como referência:

- HTML/CSS visual aproximado;
- comportamento desejado por status;
- exemplos de parsing necessários;
- decisões de microcopy;
- validações feitas com dados reais.

Não deve ser reaproveitado sem revisão:

- estrutura de classes;
- métodos atuais de domínio;
- renderização em PHP;
- acoplamento entre busca, regra e apresentação.

## Direção para a versão final

A versão final deve ser reconstruída com:

- `block_coursecardsuems.php` mínimo, apenas orquestrando o bloco;
- classes em `classes/local/` para domínio e leitura de dados;
- classes em `classes/output/` para renderables/view models;
- templates Mustache em `templates/`;
- strings em `lang/`;
- testes PHPUnit para regras de domínio;
- Behat quando o comportamento visual/interativo justificar.

Ver também: `docs/adr/0001-reconstruir-plugin-apos-prototipo.md`.
