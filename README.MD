# Simple FSM state and transition map

[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Total Downloads][badge-downloads]][downloads]

## Installation
```bash
pecl install ds
composer require sbooker/workflow
```
## Usage
```php
// Step 1. Create Status Enum

use Sbooker\Workflow\Status;

final class ConcreteStatus extends Status
{
    // define enum, see litgroup/enumerable
    public static function first(): self { 
    // ... 
    }

    public static function second(): self { 
    // ... 
    }

}

// Step 2. Define workflow

use Sbooker\Workflow\Workflow;
use Ds\Map;
use Ds\Set;

final class ConcreteWorkflow extends Workflow
{
    public function __construct()
    {
        parent::__construct(ConcreteStatus::first());
    }


    protected function buildTransitionMap(): Map
    {
        $map = new Map();
        $map->put(ConcreteStatus::first(), new Set([ConcreteStatus::second()]));
        return $map;
    }

    protected function getStatusClass(): string
    {
        return ConcreteStatus::class;
    }    
}

// Step 3. Use workflow in your entity for state control
class ConcreteEntity {
    // ...
    private ConcreteWorkflow $workflow;
    // ...

    public function __construct() {
        // ...
        $this->workflow = new ConcreteWorkflow();
        // ...
    }
    
    public function doSecond(): void {
        // ...
        $this->workflow->transitTo(ConcreteStatus::second());
        // ...
    }
} 
```

## License
See [LICENSE][license] file.

[badge-release]: https://img.shields.io/packagist/v/sbooker/workflow.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/sbooker/workflow.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/sbooker/workflow.svg?style=flat-square

[release]: https://img.shields.io/packagist/v/sbooker/workflow
[license]: https://github.com/sbooker/workflow/blob/master/LICENSE
[php]: https://php.net
[downloads]: https://packagist.org/packages/sbooker/workflow

