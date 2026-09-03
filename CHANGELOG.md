# Changelog

## 1.1.0 (2026-09-03)

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

### Alterado
- `setup_version` do módulo atualizado para `1.1.0`.
- Pacote renomeado de `konduto/magento2` para **`equifax-bvs/konduto-magento2`**
  (com `replace` do nome antigo para compatibilidade em upgrades via Composer).

