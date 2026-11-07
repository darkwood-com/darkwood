# Bonzai reconciliation

`app:bonzai:reconcile` re-reads the least recently verified known Bonzai orders
through the authenticated Business API and synchronizes their provider-neutral
Darkwood entitlements. The operation is idempotent and never associates an
order with a user. Its default batch is 200 orders; the bounded `--limit`
option can be set between 1 and 1000.

Run it hourly in production with the repository Nix environment:

```bash
cd /var/www/darkwood-com/darkwood-com
nix --extra-experimental-features 'nix-command flakes' develop --command \
  env APP_ENV=prod APP_DEBUG=0 php bin/console app:bonzai:reconcile --no-interaction
```

Bonzai currently documents retrieval by order ID, not an order-list endpoint.
Consequently, reconciliation covers orders already discovered through the
webhook or an authenticated checkout. A direct purchase whose one-shot webhook
was never received cannot be discovered automatically and requires a manual
webhook resend from Bonzai.

Bonzai exposes one creator webhook URL and the existing account notification
endpoint is owned by Navi. Navi preserves that endpoint and forwards only
`event_type` and numeric `order_id` to Darkwood through
`DARKWOOD_BONZAI_WEBHOOK_URL`. No customer name or email is forwarded. Darkwood
still treats this relay as untrusted and performs the authoritative API lookup.
The URL includes the Darkwood webhook token and must remain in production secret
configuration, never Git or logs.

The webhook remains an untrusted notification. It creates or locates the order,
then the same authenticated verification path re-reads it before any canonical
state is updated. Failures are logged with a one-way order hash, never with the
customer email, API token, or provider payload.
