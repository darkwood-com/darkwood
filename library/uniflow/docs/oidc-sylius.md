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

## Activation status

Uniflow currently runs Sylius 2.3.2, Symfony 8.1 and PHP 8.5. The identity mapping and explicit association command are ready. Browser OIDC authentication remains disabled until the project adopts a stable Symfony version providing the native `oidc_login` authenticator with verified Sylius compatibility.

No temporary OIDC bundle, provider URL or client secret is committed. Each installation supplies its discovery URL, client configuration and redirect URIs through deployment configuration.

## Activation checklist

1. Confirm Sylius and all installed plugins support the selected Symfony version.
2. Use Symfony's maintained native OIDC integration.
3. Register exact redirect URIs with the chosen identity provider.
4. Configure OIDC inside the existing `shop` firewall, retaining its provider and user checker chain.
5. Resolve the validated `sub` exclusively through `UserOAuth(provider, identifier)`.
6. Test initial URL restoration, existing provider sessions, unknown subjects, expired sessions and local logout.
7. Keep Admin, JWT and API-key authentication regression tests green.
