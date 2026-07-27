--------------------
MobileDetect
--------------------
Author: Vasily Naumkin <bezumkin@yandex.ru>
--------------------

Mobile device detection for MODX Revolution 2.8+ and 3.x.

Requirements: PHP 8.2+, mobiledetect/mobiledetectlib 4.11.x.

Features:
- HTML tags <standard>, <tablet>, <mobile> filtered on OnWebPagePrerender
- Snippet MobileDetect for conditional output
- Fenom blocks {mobile}, {phone}, {tablet}, {desktop}, {standard}
- Fenom modifier |mobiledetect
- Force mode via ?browser=standard|tablet|mobile|detect
- Example chunk tplMobileDetectSwitch

Build: run "composer install" in core/components/mobiledetect before packaging.
