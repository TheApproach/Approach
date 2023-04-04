# Install Approach
Currently, you can install Approach through the public git repository or composer. 

---
## Composer
Add the following to your **composer.json**
```json
"extra": {
    "installer-types": ["approach"],
    "installer-paths": {
        "support/lib/{$name}/": ["approach/approach"],
        "support/lib/extension/{$name}/": ["approach/extension"],
        "support/lib/community/{$name}/": ["approach/community"],
        "support/lib/vendor/{$name}/": ["approach/vendor"],
        "support/lib/wild/{$name}/": ["approach/wild"]
    }
},
"repositories": [
    {
        "type": "vcs",
        "url": "http://git.suitespace.corp/Approach/Approach"
    }
],
"config": {
    "vendor-dir": "support/lib/vendor",
    "secure-http": false,
    "allow-plugins": {
        "oomphinc/composer-installers-extender": true,
        "approach/approach": true,
        "composer/installers": true
    }
},
"require": {
    "approach/approach": "dev-master",
    "oomphinc/composer-installers-extender": "^2.0"
}
```

Run
```bash
$ composer update
```
---

## Tests

### make unit tests
```bash
$ php vendor/bin/codecept generate:cest Unit <TestName>
```

### run tests
```bash
$ php vendor/bin/codecept run [<TestName>]
```