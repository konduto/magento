# Guia de Publicação — equifax-bvs/konduto-magento2

## Visão geral

- **Pacote Composer:** `equifax-bvs/konduto-magento2` (substitui `konduto/magento2` via `replace`)
- **Módulo Magento:** `Konduto_Antifraud` (inalterado — sem migração para lojas existentes)
- O Packagist lê as versões das **tags git** do repositório vinculado. O campo `version`
  do `composer.json` deve **sempre coincidir com a tag** da release.

## Publicação de uma nova versão (release)

1. Atualizar `version` no `composer.json` e `setup_version` no `etc/module.xml`.
2. Atualizar o `CHANGELOG.md`.
3. Commit + tag + push:

   ```bash
   git commit -am "Release X.Y.Z: descrição"
   git tag X.Y.Z
   git push origin master
   git push origin X.Y.Z
   ```

4. Se o auto-update (webhook) estiver ativo, o Packagist publica sozinho.
   Caso contrário, clicar em **Update** na página do pacote.

## Setup inicial no Packagist (feito uma única vez)

1. Criar conta em <https://packagist.org> (login com GitHub, conta da equipe).
2. Garantir repositório **público** acessível ao Packagist
   (o Packagist público não lê repositórios privados — alternativa: Private Packagist).
3. Submeter em <https://packagist.org/packages/submit> com a URL do repositório.
   O vendor `equifax-bvs` fica reservado à conta que publicar primeiro.
4. Ativar auto-update:
   - GitHub App do Packagist na organização, **ou**
   - Webhook manual: *Settings → Webhooks* no repo →
     Payload URL `https://packagist.org/api/github?username=USUARIO`,
     content type `application/json`, secret = API Token do Packagist, evento `push`.
5. Adicionar co-mantenedores na página do pacote (*Maintainers*), para o pacote
   não depender de uma única conta.

## Atualização forçada via API (útil em CI)

```bash
curl -XPOST -H 'Content-Type: application/json' \
  'https://packagist.org/api/update-package?username=USUARIO&apiToken=TOKEN' \
  -d '{"repository":{"url":"https://github.com/equifax-bvs/konduto-magento2"}}'
```

## Instalação nas lojas

```bash
# migração a partir do pacote antigo
composer remove konduto/magento2

composer require equifax-bvs/konduto-magento2:^1.1
bin/magento setup:upgrade
bin/magento cache:flush
```

## Verificação pós-publicação

```bash
curl -s https://repo.packagist.org/p2/equifax-bvs/konduto-magento2.json | python3 -m json.tool | grep '"version"'
```

## Remoção / descontinuação do pacote

Como mantenedor, na página do pacote no Packagist:

- **Delete** (*Edit → Delete*): remove o pacote por completo.
  - Lojas com `composer.lock` existente continuam instalando (download vem do GitHub),
    mas `composer update`/`require` passam a falhar.
  - O nome excluído fica **bloqueado** no Packagist (nenhuma outra conta pode
    registrá-lo — proteção contra supply-chain attack).
  - Usar apenas se ninguém depende do pacote.
- **Abandon** (*Edit → Abandon*): marca como abandonado, opcionalmente indicando
  um pacote substituto.
  - O pacote continua instalável; o Composer exibe aviso de descontinuação.
  - Opção recomendada quando já existem lojas usando.
- **Congelar**: simplesmente não publicar novas tags — o pacote permanece
  disponível na última versão.


