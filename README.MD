[Read in English](README.EN.MD)

# Workflow (`sbooker/workflow`)

[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![PHP Version][badge-php]][php]
[![Total Downloads][badge-downloads]][downloads]

Простая, но строгая реализация паттерна "Конечный автомат" (State Machine) для доменных объектов и не только.

## Назначение библиотеки

Во многих приложениях состояние объекта (статус заказа, заявки, документа) — это просто поле в базе данных. Логика, разрешающая или запрещающая смену статуса, часто разбросана по разным частям кода, что приводит к ошибкам и усложняет поддержку.

Эта библиотека решает проблему, **формализуя управление жизненным циклом объекта**. Она заставляет вас явно определить:
1.  Все возможные **состояния** (статусы), в которых может находиться объект.
2.  Все разрешенные **переходы** между этими состояниями.

Логика переходов инкапсулируется внутри самого объекта, что делает его поведение предсказуемым и надежным.

## Ключевые особенности

*   **Явное определение переходов:** Карта переходов (`TransitionMap`) — единственный источник правды о жизненном цикле объекта.
*   **Инкапсуляция логики:** Объект сам отвечает за смену своих состояний.
*   **Надежные статусы-объекты:** Построена на [litgroup/enumerable](https://github.com/LitGroup/enumerable.php), что позволяет создавать типобезопасные и легко сохраняемые в БД статусы.
*   **Эффективные структуры данных:** Использует `Ds\Map` и `Ds\Set` для удобного и типобезопасного определения карты переходов (объект статуса -> набор объектов статусов). Работает 'из коробки' с PHP-реализацией (polyfill), но для максимальной производительности рекомендуется C-расширение `ds`.
*   **Защита от невалидных состояний:** Попытка выполнить недопустимый переход приведет к контролируемой ошибке `FlowError`.

## Установка

```bash
composer require sbooker/workflow
composer require litgroup/enumerable
composer require php-ds/php-ds
```
Библиотека зависит от пакета `php-ds/php-ds`, который предоставляет структуры данных `Map` и `Set`. По умолчанию используется PHP-реализация (polyfill), которой достаточно для большинства сценариев.

**Для максимальной производительности (опционально)** вы можете дополнительно установить C-расширение:
```bash
pecl install ds
```

## Быстрый старт

Рассмотрим пример управления жизненным циклом Заказа (`Order`).

### Шаг 1: Определите Статусы

Создайте класс статуса, унаследовав его от `Sbooker\Workflow\Status`.

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

### Шаг 2: Создайте класс Workflow

Унаследуйте свой класс от `Sbooker\Workflow\Workflow` и реализуйте два абстрактных метода.

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
        // Начальный статус - неотъемлемая часть определения этого жизненного цикла.
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

### Шаг 3: Интегрируйте Workflow в ваш Агрегат

Ваш доменный объект просто создает экземпляр Workflow, не зная ничего о его начальном состоянии и правилах его изменения.

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

### Шаг 4: Используйте в прикладном коде

```php
// src/UseCase/PayOrder/Handler.php
use Sbooker\Workflow\FlowError;

$order = $orderRepository->get($orderId); // Заказ в статусе 'NEW'

try {
    $order->pay(); // Успешно! Статус изменится на 'PAID'

    // Эта строка вызовет исключение FlowError
    $order->pay();

} catch (FlowError $e) {
    // Обрабатываем ошибку бизнес-логики
    $this->logger->error("Invalid status transition: " . $e->getMessage());
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

