# Deployment Contract

## Environments

- Development: Local WP
- Source: GitHub
- Pre-launch: Local → Hostinger staging → Hostinger production
- Post-launch code path: Local → GitHub → staging → production

## Data boundaries

Keep these separate:

- **Code:** the custom theme and project tools in this repository.
- **Database:** WordPress and WooCommerce records, including products, customers and orders.
- **Media:** WordPress uploads and licensed media assets.
- **Environment configuration:** site URLs, runtime settings and server configuration.
- **Secrets:** credentials, salts, payment keys and API keys; never commit them.

## Permanent production rule

Once production receives real customers or orders, never overwrite the production WooCommerce database with a Local or staging database. Deploy code and approved media/configuration changes separately, using backups and a deliberate migration plan for any data change.

## Release expectation

The production theme ZIP contains exactly one top-level directory: `swimshop-zimbabwe/`, sourced from `theme/swimshop-zimbabwe/`. Coordination documents and repository tools are never packaged into the theme ZIP.

