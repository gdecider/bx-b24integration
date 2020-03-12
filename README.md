# Интеграция сайта с Б24

## Настройка

* создайте веб-хук в вашем Б24
  * **Приложения → Вебхуки → Добавить вебхук**
  * При добавлении выбрать **Входящий вебхук**

* Скопируйте файл ```/bitrix/.settings_extra.example.php```, переименовав его в ```.settings_extra.php```

* Заполните настройки секции ```b24_integration``` в файле ```/bitrix/.settings_extra.php```

## Пример использования

```php
<?php

$b24i = \Local\Integration\B24Integration::getInstance();

// запросить список полей доступных для заполнения лида
$leadFields = $b24i->crmLeadFields();

// пробросить лид в Б24
$leadAddResult = $b24i->crmLeadAdd([
    'TITLE' => 'some title',
    'NAME' => 'name',
    'LAST_NAME' => 'lastname',
    'EMAIL' => 'email@example.com',
    'PHONE' => '+7 (900) 000-00-00',
    'COMMENTS' => 'test comment',
]);

```

## Расширение

Сейчас в классе написаны методы лишь для работы с 2мя ф-ями, при необходимости на их основе можно дописать нужные методы.
Официальная документация по REST методам Б24 тут [https://dev.1c-bitrix.ru/rest_help/crm/index.php](https://dev.1c-bitrix.ru/rest_help/crm/index.php)
