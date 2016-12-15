# Symfony 3 Elastic Bundle
## Installation
### Composer
```sh
composer require ronte-ltd/elastic-bundle
```
### AppKernel.php
```php
new RonteLtd\ElasticBundle\RonteLtdElasticBundle()

```
### config.yml
```yaml
ronte_ltd_elastic:
    entities:
        AppBundle\Entity\Entity: "%kernel.root_dir%/../src/AppBundle/Resources/schema/entity.yml"
    hosts:
        - 'http://127.0.0.1:9200'
```

### Example of schema of entity that the entity.yml at above
```yaml
index: 'items'
type: 'item'
settings:
    number_of_shards: 3
    number_of_replicas: 2
mappings:
    _source:
        enabled: true
    properties:
        id:
            type: 'integer'
        name:
            type: 'string'
            analyzer: 'standard'
        nickname:
            type: 'string'
            analyzer: 'standard'
```
## Road map
- [x] Event Listener for events of doctrine
- [x] Elastic Service
- [x] Tests