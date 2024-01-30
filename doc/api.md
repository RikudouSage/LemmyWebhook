# API management

This package contains an api for webhook management. It runs inside the container on port 80.

### Authentication / authorization

By default, users cannot register their own account, it can be enabled by setting the `API_REGISTRATION_ENABLED`
environment variable to `1`. When enabled, the account still has to be manually approved (either in db or through the api).

> In DB: `update users set enabled = true where id = :userId`

> Using API: `curl -H "Authorization: Bearer [apiKey]" -XPATCH http://localhost/rest/users/{userId} -d '{"data": {"type": "users", "id": {userId}, "attributes" {"enabled": true}}}'`

When a user is authorized, they only have access to webhooks created by them, unless they're an admin, in that case they
have access to all webhooks, including those without any user associated.

The api endpoints for authentication/authorization can be found at [openapi.yaml](../openapi.yaml).

### REST api

After successful authorization, you can call the `/rest` endpoints which follow the [JSON:API v1.0](https://jsonapi.org/format/1.0/) standard.

Api resources available:

- `users`
- `scopes`
- `webhooks`
