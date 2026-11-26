# OIDC authentication with Sylius

Uniflow keeps Sylius as its application and user-management foundation. An installation may delegate Shop authentication to an OpenID Connect provider while retaining local business permissions and data.

## Identity mapping

The stable OpenID Connect `sub` claim is stored through Sylius' existing `UserOAuth` model:

- `provider`: a stable installation-defined provider name;
- `identifier`: the exact OIDC `sub`;
- `user`: the local Uniflow `ShopUser`.

The local Sylius identifier and UUID remain unchanged. E-mail addresses are never used as the permanent technical link key. A database uniqueness constraint on `(provider, identifier)` prevents one external identity from being linked to multiple local users.

Associations are administrative and explicit:

```console
php bin/console app:user:link-oidc <shop-user-uid> <provider> <subject>
```

Use `--replace` only after verifying both identities when replacing an existing association. The command does not create a user and does not match by e-mail.

## Target authentication flow

Browser authentication should use Authorization Code Flow with PKCE S256, `state` and `nonce`. ID tokens must be validated for signature, issuer, audience and expiry. Uniflow sessions and cookies remain independent from the identity provider.

Only the Sylius `shop` firewall should delegate browser authentication by default. The Sylius `admin` firewall, JWT API firewalls and API-key authenticators remain separate unless an installation explicitly chooses otherwise.

Unknown or unlinked subjects are denied by default. There is no automatic provisioning or automatic e-mail linking. Local logout invalidates only the Uniflow session unless global logout is explicitly configured and tested.

`OidcShopUserProvider` implements Symfony's attributes-based user-provider contract. It resolves the validated identifier exclusively through the configured `OIDC_PROVIDER` and Sylius `UserOAuth`, then returns the existing `ShopUser`. It never creates an account or updates identity data from OIDC claims. The existing Sylius Shop user checker remains responsible for rejecting disabled users.

## Darkwood Identity rollout

Uniflow runs the Darkwood browser pilot on Symfony 8.1. The Shop callback
validates the authorization code, PKCE S256, state, nonce, issuer,
audience and ID-token signature. It selects the existing ShopUser using
the validated `sub` and `ShopUser.darkwoodUid`. The signed
`email_verified` claim and an address matching the associated Sylius
Customer are required. The callback keeps the Sylius verification and
enabled checks and updates `verified_at` after that proof. The existing
`UserOAuth` provider and explicit association command remain available
for other integrations.

Configure `DARKWOOD_OIDC_ISSUER`, `DARKWOOD_OIDC_CLIENT_ID`,
`DARKWOOD_OIDC_CLIENT_SECRET` and `DARKWOOD_OIDC_REDIRECT_URI` with the
deployment’s private configuration. The confidential client accepts
authorization code with scopes `openid email` at the exact
`https://uniflow.io/auth/darkwood/callback` URI.

For a pilot, set `UNIFLOW_OIDC_PILOT_UID` to a single Darkwood subject and
keep `UNIFLOW_OIDC_ALLOW_LINKED` unset or false. A migrated user with a
`uf_` Darkwood username and no password can choose their password via
the [Darkwood reset form](https://darkwood.com/resetting/request).
The one-time link is bound to their current mailbox and verifies its
possession when the new password is saved.

For a wider rollout, confirm mail delivery before announcement. Set
`UNIFLOW_OIDC_ALLOW_LINKED=true` to offer the Darkwood button to every
linked, enabled account that completes address verification. The
existing Sylius login is available throughout this phase. Monitor
activation, successful callbacks and both login options before any
change to the default entry point. Removing the pilot UID and
setting the rollout flag to false disables the OIDC routes.

Admin, JWT API and API-key authentication use their existing firewalls.
The test pilot uses a dedicated ShopUser and avoids payment actions.
