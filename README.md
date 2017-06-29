# imposter
Tool to fake GitHub wehbook events.

## Setup
- Install dependencies with `composer install`.
- Duplicate the `.env.example` file to `.env`
- Adjust settings in `.env`

## Usage
To spoof a pull request merge event, use:
```
php spoof styxit/imposter myBrach develop
```

This will fake an event as if branch "mybranch" was merged into branch "develop", for repository "styxit/imposter".

Use `php spoof --help` to get more info about the command.

## Config
In the `.env` the following options can be specified:

##### DESTINATION_URL
This is the url to which the event is POST-ed. Usually your own webhook.

##### GITHUB_SECRET
The secret string you provided when setting up the webhook at GitHub. This used to sign your request.
