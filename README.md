# Пример компонента грида Bitrix24

Готовый шаблон компонента для Битрикс (D7), реализующий таблицу (grid) на базе
`Bitrix\Main\UI\Grid\Options` и ORM-сущности инфоблока. В репозитории два
компонента: один только с гридом, второй — с гридом и фильтром.

## Что внутри

- `local/components/mymodule/myelement.list` — список элементов инфоблока в виде
  грида с постраничной навигацией, настраиваемым размером страницы и действиями
  над строками (например, удаление) под правами текущего пользователя.
- `local/components/mymodule/myelement.filter.list` — то же самое, плюс панель
  фильтрации (`Bitrix\Main\UI\Filter`): поиск по названию, фильтр по статусу
  (список значений свойства `STATUS`) и по дате создания.
- `example/` и `example_with_filter/` — минимальные примеры подключения
  компонентов на отдельной странице.
- Ajax-действия (`deleteAction` и т. п.) реализованы через
  `Controllerable`/`ActionFilter\Authentication`, поэтому работают без
  перезагрузки страницы.

## Установка

1. Скопируйте каталог `local/` в корень вашего проекта на Bitrix, чтобы
   компоненты оказались в `local/components/mymodule/`.
2. Скопируйте содержимое `example/` или `example_with_filter/` (по желанию) в
   нужный раздел сайта — это примеры страниц, на которых компонент уже
   подключён.

## Как адаптировать под свой инфоблок

1. Переименуйте каталоги `mymodule/`, `myelement.list/`, `myelement.filter.list/`
   и имя класса компонента (`MyElementListComponent`,
   `MyElementFilterListComponent`) на свои.
2. В обоих компонентах замените ORM-класс сущности
   `Bitrix\Iblock\Elements\ElementMyElementTable` (и обращения к нему) на свой —
   достаточно сгенерированной сущности нужного инфоблока.
3. Опишите колонки в методах `getGridColumns()` и (для варианта с фильтром)
   поля фильтра в `getFilterFields()`. Названия колонок хранятся в
   `lang/<ru|en>/class.php` — обновите их под свою предметную область.
4. Для фильтра по статусу убедитесь, что в инфоблоке есть свойство с кодом
   `STATUS` типа «Список» (либо поменяйте код в `getStatuses()` и `getFilterFields()`).
5. Если добавляете новые действия над строками — реализуйте соответствующие
   `*Action`-методы и зарегистрируйте их в `configureActions()`.

## Подключение на странице

```php
<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Мой список");
$APPLICATION->IncludeComponent(
    "mymodule:myelement.list",
    ".default",
    []
);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
```

Для варианта с фильтром замените `mymodule:myelement.list` на
`mymodule:myelement.filter.list` — пример см. в `example_with_filter/index.php`.

## Требования

- «Битрикс24» с поддержкой D7 и ORM (генерация сущностей
  инфоблоков).
- PHP 8.0+ (используется типизация свойств и `int`/`array` в сигнатурах).
