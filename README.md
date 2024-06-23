# SymfonySecurityDoctrineFilterBundle

Symfony Voter - это замечательный инструмент, чтобы управлять разрешениями Вашего приложения.

Однако, они не позволяют отфильтровать данные при получении из базы данных в зависимости от прав пользователя. Для этого
приходится либо фильтровать данные уже после получения, либо реализовывать логику фильтрации отдельно.

SymfonySecurityDoctrineFilterBundle - это попытка подружить Symfony Voter и Doctrine ORM QueryBuilder, чтобы одна и та
же логика использовалась как для фильтрации данных, так и для проверки прав доступа к этим самым данным.

## Установка

```bash
composer require kenny1911/symfony-security-doctrine-filter-bundle
```

## Использование

Описание Doctrine ORM сущностей для примера:

```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Post {
  #[ORM\ManyToOne()]
  private User $author;
  
  // ...
}

#[ORM\Entity]
class User implements \Symfony\Component\Security\Core\User\UserInterface { /* ... */ }
```

Фильтрация `QueryBuilder` для текущего пользователя:

```php
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterManager;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterSubject;

$qb = $container
    ->get('doctrine')
    ->getManagerForClass(Post::class)
    ->createQueryBuilder()
    ->from(Post::class, 'p')
    ->select('p')
;

// Вернет все сущности Post
$qb->getQuery()->getResult();

$currentUser = $container->get('security.token_storage')->getToken()->getUser();

/** @var FilterManager $filterManager */
$filterManager = $container->get('symfony_security_doctrine_filter.filter_manager');
$filterManager->filter('can_read', $qb, new FilterSubject(Post::class, 'p'), $user);

// Вернет только те сущности Post, которые доступны текущему Пользователю по атрибуту can_read
$qb->getQuery()->getResult();
```

Проверка прав на основе фильтрации:

```php
$authorizationChecker = $container->get('security.authorization_checker');
$post = $container->get('doctrine')->getManagerForClass(Post::class)->find(1);

// Вернет true, если у текущего Пользователя есть права can_read для сущности Post с id 1
$authorizationChecker->isGranted('can_read', $post);
```

## Как это работает

### `FilterManager`

Интерфейс `\Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterManager` содержит лишь один
метод `filter`, который добавляет в `QueryBuilder` дополнительные условия, чтобы возвращать результаты только для
выбранного пользователя. Этот метод принимает следующие аргументы:

- `$attribute` - атрибут, по которому следует фильтровать записи.
- `$qb` - экземпляр QueryBuilder, которому необходимо добавить условия.
- `$subject` - экземпляр FilterSubject, содержащий класс сущности и ее алиас в $qb.
- `$user` - текущий пользователь.

Единственная реализация этого интерфейса - это `\Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\DoctrineOrmFilterManager`.

### `Filter`

`DoctrineOrmFilterManager` применяет специальные фильтры
`\Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\Filter` для `QueryBuilder`. К одному
`QueryBuilder` можно применить несколько фильтров.

Метод `Filter::apply()` возвращает список из `FilterCondition` - специальный объект, указывающий `FilterManager`, какие
добавить join, условия и параметры в `QueryBuilder`.

### `FilterVoter`

Класс `\Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Core\Authorization\Voter\FilterVoter` - это реализация
Symfony Voter, которая использует `FilterManager` для проверки прав пользователя к Doctrine сущности.

### Написание собственных фильтров

Чтобы добавить свои правила для фильтрации сущностей и проверки прав, следует создать свой фильтр сервис, который
реализует интерфейс `Filter`.

Для удобства можно наследоваться от `\Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\BaseFilter`.

Пример фильтр сервиса:

```php
namespace App\Security\AccessFilter;

final class CanReadPostFilter extends BaseFilter
{
    protected function supports(string $attribute, ReadableQueryBuilder $qb, FilterSubject $subject, mixed $user): bool
    {
        return 'can_read' === $attribute && $subject->className === Post::class;
    }
    
    protected function doApply(string $attribute, ReadableQueryBuilder $qb, FilterSubject $subject, mixed $user): array
    {
        $alias = self::uniqueAlias($qb, 'u');
        $param = self::uniqueParamName($qb, 'userId');
        
        $condition = $this->createConditionBuilder()
            ->join('p.author', "{$alias}")
            ->setCondition("{$alias}.id = :{$param}")
            ->setParameter($param, $user)
            ->build()
        ;

        return [
            $condition,
        ]
    }
}
```

В данном примере происходит следующее:
- Фильтра сами не могут изменять `QueryBuilder`, они лишь указывают на то, как следует его изменить, Поэтому вместо
оригинального `QueryBuilder` используется обертка `ReadableQueryBuilder`. Благодаря этому можно получить части запроса,
но не изменять его.
- В методе `supports` указывается, что фильтр применяется для сущностей `Post` для атрибута `can_read`.
- Статические методы uniqueAlias и uniqueParamName нужны ждя того, чтоб сгенерировать уникальное имя алиаса и параметра
в рамках текущего `QueryBuilder`. Это специальные методы-обертки над утилитой `UniqueNamer`.
- `FilterConditionBuilder` - это билдер для `FilterCondition`. Создается он с помощью вспомогательного метода
`createConditionBuilder`.
- Фильтр может возвращать несколько условий (`FilterCondition`).

После создания класса фильтра, следует создать сервис и добавить к нему тег
`symfony_security_doctrine_filter.filter`.

Пример yaml конфигурации:

```yaml
services:
  app.access_filter.can_read_post_filter:
    class: App\Security\AccessFilter\CanReadPostFilter
    tags:
      - name: `symfony_security_doctrine_filter.filter`
```

## Лицензия

Этот пакет выпущен под лицензией MIT.
