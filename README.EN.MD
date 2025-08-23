[Read in Russian](README.md)

# Workflow (`sbooker/workflow`)

[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Total Downloads][badge-downloads]][downloads]

A simple yet strict implementation of the "State Machine" pattern for domain objects and beyond.

## Library's Purpose

In many applications, an object's state (the status of an order, request, or document) is just a field in the database. The logic that allows or forbids a status change is often scattered across different parts of the code, leading to errors and making maintenance difficult.

This library solves the problem by **formalizing the object's lifecycle management**. It forces you to explicitly define:
1.  All possible **states** (statuses) an object can be in.
2.  All permitted **transitions** between these states.

The transition logic is encapsulated within the object itself, making its behavior predictable and reliable.

## Key Features

*   **Explicit Transition Definitions:** The transition map (`TransitionMap`) is the single source of truth for the object's lifecycle.
*   **Encapsulated Logic:** The object itself is responsible for changing its states.
*   **Robust Status Objects:** Built on [litgroup/enumerable](https://github.com/litgroup/php-enumerable), allowing for the creation of type-safe statuses that are easy to persist to a database.
*   **Efficient Data Structures:** Uses `Ds\Map` and `Ds\Set` for a convenient and type-safe way to define the transition map (status object -> set of status objects). It works out of the box with the PHP implementation (polyfill), but the `ds` C-extension is recommended for maximum performance.
*   **Invalid State Protection:** An attempt to perform an invalid transition will result in a controlled `FlowError`.

## Installation

```bash
composer require sbooker/workflow
composer require litgroup/enumerable
composer require php-ds/php-ds
```
The library depends on the `php-ds/php-ds` package, which provides `Map` and `Set` data structures. By default, the PHP implementation (polyfill) is used, which is sufficient for most scenarios.

**For maximum performance (optional)**, you can also install the C-extension:
```bash
pecl install ds
```

## Quick Start

Let's consider an example of managing an Order's lifecycle.

### Step 1: Define the Statuses

Create a status class that extends `Sbooker\Workflow\Status`.

```php
// src/Orders/Domain/Status.php
namespace App\Orders\Domain;

use Sbooker\Workflow\Status as BaseStatus;

final class Status extends BaseStatus
{
    private const NEW = 'new';
    private const PAID = 'paid';
    private const SENT = 'sent';
    
    public static function new(): self
    {
        return new self(self::NEW);
    }
    
    public static function paid(): self
    {
        return new self(self::PAID);
    }
    
    public static function sent(): self
    {
        return new self(self::SENT);
    }
}
```

### Step 2: Create the Workflow Class

Extend your class from `Sbooker\Workflow\Workflow` and implement the two abstract methods.

```php
// src/Orders/Domain/OrderWorkflow.php
namespace App\Orders\Domain;

use Sbooker\Workflow\Workflow;
use Ds\Map;
use Ds\Set;

final class OrderWorkflow extends Workflow
{
    public function __construct()
    {
        // The initial status is an integral part of this lifecycle's definition.
        parent::__construct(Status::new());
    }

    protected function getStatusClass(): string
    {
        return Status::class;
    }

    protected function buildTransitionMap(): Map
    {
        return new Map([
            Status::new() => new Set([ Status::paid() ]),
            Status::paid() => new Set([ Status::sent() ]),
        ]);
    }
}
```

### Step 3: Integrate the Workflow into Your Aggregate

Your domain object simply creates an instance of the Workflow, knowing nothing about its initial state or transition rules.

```php
// src/Orders/Domain/Order.php
namespace App\Orders\Domain;

final class Order
{
    private OrderWorkflow $workflow;

    public function __construct()
    {
        $this->workflow = new OrderWorkflow();
    }

    public function pay(): void
    {
        $this->workflow->transitTo(Status::paid());
    }

    public function getStatus(): Status
    {
        return $this->workflow->getStatus();
    }
}
```

### Step 4: Use It in Your Application Code

```php
// src/UseCase/PayOrder/Handler.php
use Sbooker\Workflow\FlowError;

$order = $orderRepository->get($orderId); // Order is in 'NEW' status

try {
    $order->pay(); // Success! Status changes to 'PAID'

    // This line will throw a FlowError
    $order->pay();

} catch (FlowError $e) {
    // Handle the business logic error
    $this->logger->error("Invalid status transition: " . $e->getMessage());
}
```

## License
See [LICENSE][license] file.

[badge-release]: https://img.shields.io/packagist/v/sbooker/workflow.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-php]: https://img.shields.io/packagist/php-v/sbooker/workflow.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/sbooker/workflow.svg?style=flat-square

[release]: https://packagist.org/packages/sbooker/workflow
[license]: https://github.com/sbooker/workflow/blob/master/LICENSE
[php]: https://php.net
[downloads]: https://packagist.org/packages/sbooker/workflow