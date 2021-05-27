# My Cache library (work in progress)

### require in composer: 
```json
{
    "require":
    {
        "peeperklip/caching": "dev-master"
    },
    "repositories": [
        { "type": "vcs", "url": "git@github.com:peeperklip/Caching.git" }
    ]
}
```

### Developing locally
This repository comes with the (stripped) dockerfiles as seen in [peeperklip/DockerFiles](https://github.com/peeperklip/DockerFiles)

### Set up
#### initial set up does not work on docker (this is WIP as well)
```shell
git clone git@github.com:peeperklip/Caching.git
php composer.phar install
docker-compose up (-d)
```

### Running the tests
```shell
# integration tests
docker-compose exec php vendor/bin/phpunit -c tests/phpunit.xml --testsuite integration

# unit tests
docker-compose exec php vendor/bin/phpunit -c tests/phpunit.xml --testsuite unit
```
