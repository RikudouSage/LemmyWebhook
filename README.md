# Lemmy Webhooks

Add efficient webhook support to your Lemmy instance. Especially useful for bots and AutoModerators.

<!-- TOC -->
* [Lemmy Webhooks](#lemmy-webhooks)
  * [Installation](#installation)
  * [Usage](#usage)
  * [Expressions](#expressions)
    * [Basic vs enhanced expressions](#basic-vs-enhanced-expressions)
    * [Example filter expressions](#example-filter-expressions)
      * [Only local users](#only-local-users)
      * [Only non-local users](#only-non-local-users)
      * [Only specific user](#only-specific-user)
      * [Contains a specific user mention (case-insensitive)](#contains-a-specific-user-mention-case-insensitive)
      * [Only if the text changed on UPDATE](#only-if-the-text-changed-on-update)
      * [The comment's hierarchy has been resolved](#the-comments-hierarchy-has-been-resolved)
    * [Example body expressions](#example-body-expressions)
      * [Pass the whole object](#pass-the-whole-object)
      * [Post title and whether the post contains URL](#post-title-and-whether-the-post-contains-url)
      * [The user's id and their ban reason (or null if no ban reason or user not banned)](#the-users-id-and-their-ban-reason-or-null-if-no-ban-reason-or-user-not-banned)
    * [Title, community and instance](#title-community-and-instance)
    * [Comment ID and a custom string](#comment-id-and-a-custom-string)
    * [Example enhanced filters](#example-enhanced-filters)
      * [Check whether the comment is posted to a community on your instance](#check-whether-the-comment-is-posted-to-a-community-on-your-instance)
  * [Full example](#full-example)
<!-- TOC -->

## Installation

Make the docker image part of your docker-compose stack, add this to your compose file:

```yaml
services:
  # ...
  redis:
    image: redis
    ports: # you don't need to bind ports if you don't want to
      - 6379:6379

  webhooks:
    image: ghcr.io/rikudousage/lemmy-webhook:latest
    environment:
      - LEMMY_HOST=postgres # the hostname of the postgres database
      - REDIS_HOST=redis # the hostname of the redis server, you can use the above redis container if you define it as part of this stack
      - LEMMY_PASSWORD=superSecr3t # the password to the postgres database
      - API_REGISTRATION_ENABLED=1 # whether to allow users to register themselves via the api
      - CORS_ALLOW_ORIGIN=^.*$$ # a regex for cors (you need to escape $ with another $)
      - LARGE_PAYLOAD_SIZE=1024 # payloads larger than this size (in bytes) will be stored in a temporary table instead of fed directly to the consumer, default is 4096. If set to 0, all payloads will be stored.
    ports:
      - 8080:80 # you can skip this, if you don't use the management api
    volumes:
      - ./volumes/database:/opt/database # bind a directory where the SQLite database will be created
```

Afterwards, run `docker-compose up -d` and you're done!

> The `LARGE_PAYLOAD_SIZE` is important to avoid "payload string too long" errors in Postgres. By default, Postgres allows 8000 bytes in the payload.

## Usage

You can either use the api, or insert webhooks directly into the database. You can read more on the api [at a separate readme](doc/api.md).

The table is quite simple and consists of these fields:

- `url` - the URL of the webhook
- `method` - can be `GET`, `POST`, `PATCH`, `DELETE`, `PUT` (taken from the [RequestMethod](src/Enum/RequestMethod.php) enum)
- `body_expression` (optional) - an expression that will be converted to JSON and sent as a body of the request, more on expressions below
- `filter_expression` (optional) - an expression that must evaluate to true if this webhook is to run, more on expressions below
- `object_type` - the type of object this webhook is interested in, currently:
  - `post`
  - `comment`
  - `instance`
  - `private_message` (only `INSERT` operation)
  - `person`
  - `registration_application`
  - `private_message_report`
  - `local_user`
  - `community_follower` - a subscription by a user to a community
- `operation` (optional) - the kind of operation this webhook is interested in, can be `INSERT`, `UPDATE`, `DELETE` (taken from the [DatabaseOperation](src/Enum/DatabaseOperation.php) enum)
- `headers` (optional) - a JSON object with keys as header names and values as header values
- `enhanced_filter` (optional) - an expression that must evaluate to true if this webhook is to run, more on expressions below
- `enabled` - whether the webhook is enabled or not

## Expressions

Expressions allow better interaction with the webhooks, for example filtering and setting the request body.

The basic syntax is very similar to JavaScript.
In every expression you have access to the `data` variable which contains the fields of the object the webhook was triggered for.

This is an example data object:

```json
{
  "timestamp": {
    "date": "2024-01-05 23:15:09.811926",
    "timezone_type": 1,
    "timezone": "+00:00"
  },
  "operation": "INSERT",
  "schema": "public",
  "table": "comment",
  "data": {
    "id": 4763628,
    "creatorId": 2,
    "postId": 4435272,
    "content": "teeest",
    "removed": false,
    "deleted": false,
    "apId": "http://changeme.invalid/52570b072a832e6a986330de",
    "local": true,
    "distinguished": false,
    "path": "0.123.456"
  },
  "previous": null
}
```

> Note that the `timestamp` property is in fact a PHP [DateTimeImmutable](https://www.php.net/manual/en/class.datetimeimmutable.php) object,
> including its methods and properties, the above is just its JSON representation.

So for example, if you only want to trigger a webhook for comments by local users, you would use this as your filter expression:

`data.data.local`

The `timestamp`, `operation`, `schema` and `table` properties have the same structure for every type of object, but the `data` property varies
based on what you're being notified about. Here's a list of all `table` values currently possible and link to the DTO that will be passed as the
`data` property:

- `post` - [PostData](src/Dto/RawData/PostData.php)
- `comment` - [CommentData](src/Dto/RawData/CommentData.php)
- `instance` - [InstanceData](src/Dto/RawData/InstanceData.php)
- `private_message` - [PrivateMessageData](src/Dto/RawData/PrivateMessageData.php)
- `person` - [PersonData](src/Dto/RawData/PersonData.php)
- `registration_application` - [RegistrationApplicationData](src/Dto/RawData/RegistrationApplicationData.php)
- `private_message_report` - [PrivateMessageReportData](src/Dto/RawData/PrivateMessageReportData.php)
- `local_user` - [LocalUserData](src/Dto/RawData/LocalUserData.php)
- `community_follower` - [CommunitySubscriptionData](src/Dto/RawData/CommunitySubscriptionData.php)

If the operation is an UPDATE, you'll also get access to the `previous` property which contains the data from the previous version of the object.
If the operation is not an UPDATE, the `previous` property is `null`.

### Basic vs enhanced expressions

There are two kinds of expressions, basic and enhanced. Enhanced expressions have access to additional functions
for interacting with the database, while simple expressions are limited to accessing only the `data` variable and
a few simple functions.

Simple expressions have access to these functions:

- `lowercase(text)` - returns the string converted to lowercase
- `transliterate(text)` - returns the string transliterated to standard latin characters:
  - example: `transliterate("Hélľö, hów ärě ýöů?")` -> `Hello, how are you?`
  - example: `transliterate("𝐻𝐞𝒍𝓁𝓸 𝔱𝕙𝖊𝗋𝚎!")` -> `Hello there!`
- `merge(arrayOrDictionary1, arrayOrDictionary2, ..., arrayOrDictionaryN)` - recursively merges an arbitrary number of arrays or dictionaries
- `comment_parent_id(commentOrPath)` - returns the comment's parent id as an integer or null if it's a top level comment, can accept either the whole comment data object, or just the path

> Note: Previous version contained the function `string_contains`. The function still exists for backwards compatibility, but shouldn't be used for new stuff, instead use the built-in `contains` like this:
> `"some string" contains "another string"`, e.g. `data.data.content contains '@my_bot@my_instance'`

Enhanced expressions, in addition to the above, have access to these functions:

- `community(communityId)` - returns the [CommunityData](src/Dto/RawData/CommunityData.php) DTO for community with given ID (or null if no such community exists)
- `instance(instanceId)` - returns the [InstanceData](src/Dto/RawData/InstanceData.php) DTO for instance with given ID (or null if no such instance exists)
- `post(postId)` - returns the [PostData](src/Dto/RawData/PostData.php) DTO for post with given ID (or null if no such post exists)
- `person(personId)` - returns the [PersonData](src/Dto/RawData/PersonData.php) DTO for a person with given ID (or null if no such person exists)
- `comment(commentId)` - returns the [CommentData](src/Dto/RawData/CommentData.php) DTO for a comment with given ID (or null if no such comment exists)
- `local_user(userId)` - returns the [LocalUserData](src/Dto/RawData/LocalUserData.php) DTO for a local user with given ID (or null if no such user exists)
- `private_message(privateMessageId)` - returns the [PrivateMessageData](src/Dto/RawData/PrivateMessageData.php) DTO for a private message with given ID (or null if no such private message exists)
- `global_ban(personId)` - returns a [ModBanData](src/Dto/RawData/ModBanData.php) DTO for the given user or `null` if no ban exists

> note that in all the cases above, null will also be returned if you don't have permission to access any of the given object types

Simple expressions can be used everywhere, but enhanced expressions cannot be used in the `filter_expression` field.
That's because `filter_expression` runs synchronously on the main thread and could potentially block further processing if it took too long.

If you need to filter on more complex expressions, you can use the `enhanced_filter` field. You can also use both fields,
it will be first filtered based on `filter_expression` on the main thread and then on the `enhanced_filter` in the worker thread.

### Example filter expressions

> The filter expressions use the Symfony ExpressionLanguage, read more on the syntax in the
> [official documentation](https://symfony.com/doc/current/reference/formats/expression_language.html).

#### Only local users

`data.data.local`

#### Only non-local users

`!data.data.local`

#### Only specific user

`data.data.creatorId === 2`

#### Contains a specific user mention (case-insensitive)

`lowercase(data.data.content) contains "@chatgpt@lemmings.world"` (I use that one for my ChatGPT bot)

#### Only if the text changed on UPDATE

`data.data.content !== data.previous.content`

#### The comment's hierarchy has been resolved

> Lemmy first creates the comment with placeholder values, for example `path` is always `0` for INSERT. You can use this expression to only trigger when the final path has been resolved.

`data.data.path !== data.previous.path`

### Example body expressions

#### Pass the whole object

`data`

#### Post title and whether the post contains URL

`{title: data.data.name, hasUrl: data.data.url !== null}`

#### The user's id and their ban reason (or null if no ban reason or user not banned)

`{id: data.data.id, banReason: global_ban(data.data.id)?.reason}`

### Title, community and instance

```
{
    title: data.data.name,
    community: community(data.data.communityId).name,
    instance: instance(community(data.data.communityId).instanceId).domain
}
```

### Comment ID and a custom string

`{commentId: data.data.id, mentionedBot: "ChatGPT@lemmings.world"}` (I use that one for my ChatGPT bot)

### Example enhanced filters

#### Check whether the comment is posted to a community on your instance

`instance(community(post(data.data.postId).communityId).instanceId).domain === 'my.instance.org'`

## Full example

The webhooks work by first filtering based on your operation and type criteria, meaning if a new post is created,
all webhooks that are created with `post` as the value of `object_type` and `INSERT` as `operation` (or without any operation
specified) will be fetched.

Afterwards all webhooks are checked for their `filter_expression`, if it evaluates to `true`, the webhook is triggered in a worker.

The worker then checks for the result of `enhanced_filter` expression and continues only if it evaluates to true.

A http request is then constructed with optional body (from `body_expression`) and headers.

So, this is a full SQL insert for getting only new local posts using a POST request:

```sql
INSERT INTO webhooks (url, method, body_expression, filter_expression, object_type, operation, headers, enhanced_filter)
VALUES ('https://example.com/webhook', 'POST', 'data.data', 'data.data.local', 'comment', 'INSERT', null, null);
```
