# captchay
a CAPTCHA that supports Arabic, Urdu and Persian languages

About
======
I wrote this PHP class because I was frustrated while using other RECAPCHEs because 
they only display latin letters and words. I developed it three years ago, and while I was 
scrolling through my files this morning I realized this wasn't really published anywhere.
So here it is, I uploaded it hoping somebody will use it.

How to use it?
=======
It's simple, you can read through the included index.php file. which is a running example
of the verification system used by this library.

You can change drawing options of the canvas and other configuration like this:
``php
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
``
Read through the `class.captchay.php` file and it should be fairly easy to know how to
modify whatever options you like.



نبذة
======
قمت بكتابة هذا الكلاس بسبب الأحباط اللي وصلت إليه من استخدام مكتبات تقدم نفس الخدمة، لكن جميع المكتبات
التي قمت بتجربتها لم تكن تتوافق مع المشروع التي كنت أقوم به، هذا كان قبل 3 سنوات تقريبًا. هذه المكتبة
تعتبر الأولى من ناحية دعمها للغة العربية، الأردو، والفارسية وإظهار كلمات معينة للتحققات.

License
=======
This project is licensed under the MIT License.
