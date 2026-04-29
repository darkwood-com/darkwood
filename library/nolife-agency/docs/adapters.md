# Adapter Selection

Default mode is null adapters (local/demo-safe).

Environment variables:

- `NOLIFE_AI_ADAPTER` (`null|symfony_ai`)
- `NOLIFE_FLOW_ADAPTER` (`null|darkwood_flow`)
- `NOLIFE_NAVI_ADAPTER` (`null|darkwood_navi`)
- `NOLIFE_MEDIA_ADAPTER` (`null|media_bundle`)
- `NOLIFE_FLOW_ROOT`
- `NOLIFE_NAVI_ROOT`
- `NOLIFE_MEDIA_BUNDLE_ROOT`

Null adapters are the default aliases in `config/services.yaml`.

Real adapters remain swappable and can be selected by configuration and command intent flags.
