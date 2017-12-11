<!DOCTYPE html>
<html>
<head>
    <title>[[*pagetitle]] / [[++site_name]]</title>
    <base href="[[++site_url]]"/>
</head>
<body>

{if 'standard' | mobiledetect}
    <p>This is a <b>standard</b> view via Fenom output filter.</p>
{/if}

{if 'tablet' | mobiledetect}
    <p>This is a <b>tablet</b> view via Fenom output filter.</p>
{/if}

{if 'mobile' | mobiledetect}
    <p>This is a <b>mobile</b> view via Fenom output filter.</p>
{/if}

[[!MobileDetect:is=`1`:then=`
    <p>This is a <b>standard</b> view via Snippet tag.</p>
`:else=``?input=`standard`]]

[[!MobileDetect:is=`1`:then=`
    <p>This is a <b>tablet</b> view via Snippet tag.</p>
`:else=``?input=`tablet`]]

[[!MobileDetect:is=`1`:then=`
    <p>This is a <b>mobile</b> view via Snippet tag.</p>
`:else=``?input=`mobile`]]

<standard>
    <p>This is a <b>standard</b> view via HTML tag.</p>
</standard>
<tablet>
    <p>This is a <b>tablet</b> view via HTML tag.</p>
</tablet>
<mobile>
    <p>This is a <b>mobile</b> view via HTML tag.</p>
</mobile>

<p>Parse time: [^t^]</p>
</body>
</html>