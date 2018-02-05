# captchay
a CAPTCHA that supports Arabic, Urdu and Persian languages.

About
======
I wrote this PHP class because I was frustrated while using other CAPTCHAs. 
They only display Latin characters and words. I developed it three years ago, and while I was 
scrolling down my files this morning I realized that this has not been published anywhere.
So here it is, I uploaded it hoping that somebody will use it.

How to use it?
=======
It's simple. You can read through the included `index.php` file which is a running example
of the verification system used by this library.

You can change drawing options of the canvas and other configurations like this:

```php
<?php
$captcha = new Captchay ( 305, 60 ); // width x height
$captcha->setConfig ( 'canvas', array (
    'border-style' => 'solid',
    'border-color' => '#e1e1e1'
) );
$captcha->setConfig ( 'texture', array (
    'arcs-count'    => 30,
    'arcs-color'    => '#aaa',
) );
$captcha->setConfig ( 'font', array (
    'size'    => 18,
) );
```
Read through the `class.captchay.php` file and it should be fairly easy to know how to
modify whatever option you like.



نبذة
======
قمت بكتابة هذا الكلاس بسبب الإحباط الذي وصلت إليه عند استخدام مكتبات تقدم نفس الخدمة، لكن جميعها لم تكن متوافقة مع المشروع الذي كنت أنفذه، هذا كان قبل 3 سنوات تقريبًا. هذه المكتبة
تعتبر الأولى من ناحية دعمها للغة العربية، والأردو، والفارسية وإظهار كلمات معينة للتحققات.

License
=======
This project is licensed under the MIT License.
