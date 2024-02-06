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
    * [Example body expressions](#example-body-expressions)
      * [Pass the whole object](#pass-the-whole-object)
      * [Post title and whether the post contains URL](#post-title-and-whether-the-post-contains-url)
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
      - CORS_ALLOW_ORIGIN=^.*$ # a regex for cors
    ports:
      - 8080:80 # you can skip this, if you don't use the management api
    volumes:
      - ./volumes/database:/opt/database # bind a directory where the SQLite database will be created
```

Afterwards, run `docker-compose up -d` and you're done!

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
    "distinguished": false
  }
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

### Basic vs enhanced expressions

There are two kinds of expressions, basic and enhanced. Enhanced expressions have access to additional functions
for interacting with the database, while simple expressions are limited to accessing only the `data` variable and
a few simple functions.

Simple expressions have access to these functions:

- `string_contains(stringToSearchIn, stringToSearchFor)` - returns true if the `stringToSearchIn` contains `stringToSearchFor`
  - example filter expression: `string_contains(data.data.content, '@my_bot@my_instance')` - returns true if the comment text contains the text `@my_bot@my_instance`, basically only reacting to a mention of a bot 
- `lowercase(text)` - returns the string converted to lowercase
- `transliterate(text)` - returns the string transliterated to standard latin characters:
  - example: `transliterate("Hélľö, hów ärě ýöů?")` -> `Hello, how are you?`
  - example: `transliterate("𝐻𝐞𝒍𝓁𝓸 𝔱𝕙𝖊𝗋𝚎!")` -> `Hello there!`

Enhanced expressions, in addition to the above, have access to these functions:

- `community(communityId)` - returns the [CommunityData](src/Dto/RawData/CommunityData.php) DTO for community with given ID (or null if no such community exists)
- `instance(instanceId)` - returns the [InstanceData](src/Dto/RawData/InstanceData.php) DTO for instance with given ID (or null if no such instance exists)
- `post(postId)` - returns the [PostData](src/Dto/RawData/PostData.php) DTO for post with given ID (or null if no such post exists)

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

`string_contains(lowercase(data.data.content), "@chatgpt@lemmings.world")` (I use that one for my ChatGPT bot)

### Example body expressions

#### Pass the whole object

`data`

#### Post title and whether the post contains URL

`{title: data.data.name, hasUrl: data.data.url !== null}`

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
