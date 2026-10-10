# MobileDetect

MODX-экстра для определения типа устройства (desktop, tablet, mobile) и вывода разного контента на одной странице.

Основано на [mobiledetect/mobiledetectlib](https://github.com/serbanghita/Mobile-Detect) 4.11.x.

## Требования

| Компонент | Версия |
|-----------|--------|
| MODX Revolution | 2.8+ или 3.x |
| PHP | 8.2+ |
| pdoTools | опционально, для Fenom-блоков и модификатора |

Определение устройства строится на User-Agent и HTTP-заголовках. Режим «Desktop site» в мобильном браузере может дать неверный результат.

## Установка

### Package Manager

1. Скачайте transport-пакет `mobiledetect-2.1.1-pl.transport.zip`.
2. Установите через **Extras → Installer**.

### Вручную

Скопируйте `core/components/mobiledetect/` в `{core_path}components/mobiledetect/`, затем установите элементы через Package Manager или импорт.

### Сборка из исходников

Собирайте transport на **MODX 2.8**. Такой zip ставится и на 2.8, и на 3.x. Сборка на MODX 3 пишет в манифест `xPDO\Transport\xPDOObjectVehicle`, и установщик 2.8 пакет не принимает. Скрипт кладёт zip в `{core_path}packages/`. Запускайте его из `_build`: он подключает `build.config.php` из текущей директории.

```bash
cd core/components/mobiledetect
composer install

cd ../../../_build
MODX_BASE_PATH=/path/to/modx php build.transport.php
```

Перед сборкой `composer install` обязателен: без `vendor/` transport-пакет не соберётся.

Результат: `{core_path}packages/mobiledetect-2.1.1-pl.transport.zip` — установка через **Extras → Installer**.

Собирайте пакет на MODX 2.8 (PHP 8.1+ для самого установщика MODX). Zip, собранный на MODX 3, на 2.8 не ставится: в манифесте остаётся `xPDO\Transport\xPDOObjectVehicle`. Релиз на GitHub собирает workflow на MODX 2.8.8. На сайте по-прежнему нужен PHP 8.2+.

## Настройки

Namespace: `mobiledetect`, area: `md_main`.

| Ключ | По умолчанию | Описание |
|------|--------------|----------|
| `md_disable_plugin` | `0` | Отключить плагин |
| `md_use_cookie` | `1` | Запоминать выбор в cookie |
| `md_tablet_is_standard` | `0` | Планшеты как desktop (теги, сниппет, Fenom и API) |
| `md_force_browser_variable` | `browser` | GET-параметр для принудительного режима |
| `md_force_browser_standard` | `standard` | Значение для desktop |
| `md_force_browser_tablet` | `tablet` | Значение для tablet |
| `md_force_browser_mobile` | `mobile` | Значение для mobile |
| `md_force_browser_detect` | `detect` | Сброс cookie и автоопределение |
| `md_standard_node` | `standard` | HTML-тег для desktop |
| `md_tablet_node` | `tablet` | HTML-тег для tablet |
| `md_mobile_node` | `mobile` | HTML-тег для mobile |

Подписи настроек: lexicon `mobiledetect:setting` (en, ru).

## Использование

Плагин **MobileDetect** должен быть включён (`md_disable_plugin = 0`).

### 1. HTML-теги (OnWebPagePrerender)

```html
<standard>
    <p>Контент для desktop</p>
</standard>
<tablet>
    <p>Контент для планшета</p>
</tablet>
<mobile>
    <p>Контент для телефона</p>
</mobile>
```

Плагин удалит лишние блоки перед отдачей страницы.

### 2. Сниппет MobileDetect

```modx
[[!MobileDetect:is=`1`:then=`Контент для mobile`:else=``:input=`mobile`]]
```

Возвращает `1`, если текущий тип устройства совпадает с параметром `input` (`standard`, `tablet`, `mobile`).

### 3. Fenom (pdoTools)

В Fenom-шаблонах имена блоков `{mobile}`, `{standard}` и т.д. нельзя писать в `<code>{standard}</code>` как текст — Fenom воспримет это как открытие блока. В документации внутри шаблона используйте HTML-сущности: `&#123;standard&#125;`.

Блоки:

```fenom
{mobile}
    <p>Mobile и tablet</p>
{/mobile}

{phone}
    <p>Только телефон</p>
{/phone}

{tablet}
    <p>Только планшет</p>
{/tablet}

{desktop}
    <p>Desktop</p>
{/desktop}
```

Модификатор:

```fenom
{if 'mobile' | mobiledetect}
    <p>Текущее устройство — mobile</p>
{/if}
```

### Принудительный режим

Передайте GET-параметр (имя задаётся в `md_force_browser_variable`):

```
?browser=mobile
?browser=tablet
?browser=standard
?browser=detect
```

При включённом `md_use_cookie` выбор сохраняется в cookie.

### Пример переключателя

Chunk `tplMobileDetectSwitch` (устанавливается с пакетом):

```modx
[[$tplMobileDetectSwitch]]
```

## API для разработчиков

```php
/** @var MobileDetect $md */
$md = $modx->getService('mobiledetect', 'MobileDetect', MODX_CORE_PATH . 'components/mobiledetect/');

$device = $md->resolveDevice();       // standard | tablet | mobile
$type = $md->getDeviceType();
$md->isMobile();   // mobile или tablet
$md->isTablet();
$md->isDesktop();

$detector = $md->getDetector();     // Detection\MobileDetect
$detector->isMobile();
$detector->isTablet();
```

## Changelog

См. [core/components/mobiledetect/docs/changelog.txt](core/components/mobiledetect/docs/changelog.txt).

## Автор

Vasily Naumkin &lt;bezumkin@yandex.ru&gt;
