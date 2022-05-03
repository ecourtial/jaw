# Webhooks

Webhooks entries are stored in the _webhooks_ table each time a resource (user, configuration, category or post) is edited (creation, deletion, modification)
.

A new webhook is inserted in this table only if there is no existing unprocessed occurrence.
By unprocessed we mean:
* attempt count < 5
* and processed date to NULL.

The webhooks can be processed by the following command _app:webhook:proces_. You should manage it with Supervisor for instance.
So far the worker is not scalable, it has not been tested yet.

Note that between each tentative, the throttling period increases, as follows:

| Attempt No | Pause in seconds before the attempt |
|------------|-------------------------------------|
| 1          | 0                                   |
| 2          | 30                                  |
| 3          | 60                                  |
| 4          | 120                                 |
| 5          | 300                                 |


The worker will automatically stop after then attempts, no matter how many webhooks it has processed. Please consider it when configuring Supervisor.

## Payload

The payload of each webhook is done in REST, with the JSON format.

The content of the payload is as follows:

| Key | Contains                                                          |
|----|-------------------------------------------------------------------|
| resourceType   | The resource type (_user_, _configuration_, _category_ or _post_) |
| resourceId   | The resource id, for instance: 4                                  |
| actionType   | The action type (_created_, _edited_, _deleted_)                  |
| currentAttemptCount   | The current attempt count                                         |
| lastAttemptDate   | The last attempt date                                             |
