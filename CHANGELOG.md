# Changelog

## 1.8.0 (2026-09-03)

Revisão dos campos do payload conforme a documentação oficial
(<https://docs.konduto.com/reference/enviar-um-pedido>).

### Adicionado
- `installments` (campo obrigatório na API) enviado na raiz do pedido, obtido do
  `additional_information` do pagamento (`installments`/`cc_installments`), com fallback `1`.
- `tax_amount` enviado quando o pedido possui impostos.
- `purchased_at` enviado no formato ISO 8601 (`AAAA-MM-DDTHH:mm:ssZ`).
- Suporte a endereços **IPv6** no campo `ip` (antes apenas IPv4 era aceito).
- `payment[].amount` (recomendado) enviado com o valor total do pedido.
- `payment[].bin` enviado quando disponível no `additional_information` do pagamento.
- Dados de cartão (`expiration_date`, `status`, `last4`, `bin`) agora também enviados
  para pagamentos do tipo `debit` (antes apenas `credit`).
- Cliente *guest*: envio de `phone1`, `tax_id` (taxvat/VAT do billing), `dob` e flag `new`.
- `shopping_cart[].discount` enviado quando o item possui desconto.

### Corrigido
- **Bug crítico**: pedidos com 2 ou mais transações de pagamento eram enviados com
  `payment` nulo (payload inválido).
- `currency` agora usa a moeda do pedido (`order_currency_code`) — antes enviava a
  moeda base da loja com valores na moeda do pedido (inconsistente em multi-moeda).
- `purchased_at` gerado em UTC (`gmdate`) para coincidir com o sufixo `Z` do formato
  ISO 8601 (o Magento grava `created_at` em UTC).
- Lista `payment` é omitida quando o método de pagamento não está mapeado na
  configuração — antes era enviado `"type": false` (valor inválido pela doc e que
  causaria `InvalidArgumentException` no SDK).
- Serialização da lista `payment`: os itens agora são construídos como modelos do
  SDK (`Payment::build`) com `addField` — o `BaseModel::toJsonArray()` do SDK
  descarta campos fora de `fields()`, o que silenciosamente removia `amount`
  (credit) e `bin`/`last4`/`expiration_date` (debit) do payload.
- `customer.dob` formatado como `YYYY-MM-DD` (doc ISO 8601) — antes era enviado no
  formato bruto do Magento (`Y-m-d H:i:s`); `dob`/`tax_id` omitidos quando vazios.
- `address1`/`address2` com separadores legíveis (`Rua X, 123` / `Apto 1 - Centro`) —
  antes rua e número eram concatenados sem separador (`Rua X123`).
- `expiration_date` agora respeita o formato `MMAAAA` com zero à esquerda
  (antes meses de 1 dígito geravam ex.: `72025`).
- `unit_cost` dos itens formatado com 2 casas decimais.
- Itens do carrinho agora usam `getAllVisibleItems()` (evita duplicidade em produtos
  configuráveis/bundle).
- `Helper\Data::getDocumentNumber()` extraía o objeto do custom attribute em vez do valor.
- `Helper\Data::apiKeyIsValid()` chamava `$this->helper->log()` inexistente.
- `KondutoService::updateOrderStatus()` capturava `Exception` não qualificada
  (fatal error em runtime) — agora `\Exception`.
- `PaymentData::getCcStatus()` usava variável não inicializada quando não havia transações.
- `PlaceAfterPlugin`: propriedade `$queueManager` declarada (dynamic properties são
  deprecated no PHP 8.2+) e leitura defensiva do cookie `_kdt`.
- Null-safety em `preg_replace`/`number_format`/`strtotime` (CEP, documento, valores
  e datas nulos causariam deprecation no PHP 8.1+).

### Alterado
- `setup_version` do módulo atualizado para `1.8.0`.
- Pacote renomeado de `konduto/magento2` para **`equifax-bvs/konduto-magento2`**
  (com `replace` do nome antigo para compatibilidade em upgrades via Composer).

