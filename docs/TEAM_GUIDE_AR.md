# دليل فريق هاكاثون ويكيميديا المغرب

هذا الدليل لفريق من خمسة أشخاص، بمن فيهم قائد الفريق. الشرح بسيط، والعمل باستعمال Windows وVS Code وPHP بدون Laravel أو React. [الدليل الإنجليزي المفصل](TEAM_GUIDE_EN.md) | [إدارة الحسابات](ADMIN_GUIDE.md) | [طريقة العمل مع Git](GIT_WORKFLOW.md) | [تقسيم المهام](HACKATHON_TASKS.md).

## 1. ما هو المشروع؟

عندنا موقع عمومي يعرض المشاريع والفعاليات والأخبار والموارد وأعضاء الفريق. وعندنا لوحة إدارة خاصة لتعديل هذه المعلومات. الزائر لا يحتاج حسابا، لكن الدخول إلى لوحة الإدارة يحتاج حسابا فعالا وكلمة مرور.

**الواجهة الأمامية Frontend:** هي ما يراه الزائر: النصوص والصور والأزرار والنماذج. الملفات المستعملة هي HTML وCSS وJavaScript عادي. PHP يجهز HTML قبل إرساله إلى المتصفح. Tailwind يساعدنا على بناء ملف CSS، وليس خادما لتشغيل الموقع.

**الخلفية Backend:** هي كود PHP الذي يستقبل الطلب، يتحقق من المدخلات والصلاحيات، ويتواصل مع قاعدة البيانات. لا نعتمد على إخفاء زر فقط لمنع المستخدم من عملية غير مسموحة؛ PHP نفسه يمنع الطلب.

**قاعدة البيانات MySQL/MariaDB:** تخزن المحتوى والحسابات والإعدادات. PHP يتصل بها بواسطة PDO. نستعمل prepared statements لإرسال القيم بأمان، وليس تركيب SQL من نص أدخله المستخدم.

المسار الفعلي للطلب في التطوير:

```text
Browser -> scripts/serve.php -> public/index.php
        -> app/bootstrap.php -> routes/web.php
        -> Controller -> Model -> PDO -> MySQL/MariaDB
        -> View + Layout -> HTML -> Browser
```

عند استعمال Apache، ملف `public/.htaccess` يوجه الروابط إلى `public/index.php`. ملف `bootstrap.php` يقرأ `.env` ويجهز تحميل الكلاسات والمساعدات والجلسة والتحقق من المستخدم.

مثال: المحرر يدخل إلى لوحة الإدارة ويضيف فعالية. PHP يتحقق من الجلسة وCSRF والمدخلات والصورة، ثم يحفظ المعلومات في قاعدة البيانات. الصفحة العمومية تقرأ **نفس القاعدة**. إذا كانت الفعالية منشورة ومناسبة للعرض، تظهر عند تحديث الصفحة. المسودة والفعالية المؤرشفة لا تظهران للزوار. الخبر المبرمج للمستقبل لا يظهر قبل وقته. لا نحتاج Git push أو إعادة بناء CSS لنشر المحتوى من اللوحة.

### أين نعمل داخل المشروع؟

| المسار | محتواه |
| --- | --- |
| `public/index.php` | مدخل طلبات المتصفح ورؤوس الأمان ومعالجة الأخطاء |
| `routes/web.php` | ربط الروابط وطرق GET/POST بدوال Controllers |
| `app/Controllers/PublicController.php` | الصفحات العمومية وتغيير اللغة ونموذج التواصل |
| `app/Controllers/AuthController.php` | تسجيل الدخول والخروج |
| `app/Controllers/Admin/` | معالجة عمليات لوحة الإدارة |
| `app/Core/` | الاتصال بالقاعدة، Router، Views، Auth، CSRF، التحقق، رفع الملفات وأدوات CLI |
| `app/Models/` | استعلامات قاعدة البيانات |
| `app/Views/public/` | قوالب الموقع العمومي |
| `app/Views/admin/` | قوالب لوحة الإدارة |
| `app/Views/layouts/` | الأجزاء المشتركة بين الصفحات |
| `app/Helpers/functions.php` | دوال مثل `e()` و`url()` و`ui()` |
| `app/Lang/` | نصوص الواجهتين الفرنسية والعربية |
| `resources/css/app.css` | مصدر CSS وTailwind؛ هنا نعدل التصميم |
| `public/assets/` | CSS المبني وJavaScript والصور المحلية |
| `public/uploads/` | ملفات رفعها المستخدمون؛ لا نرسلها إلى GitHub |
| `database/migrations/schema.sql` | إنشاء الجداول الناقصة |
| `database/seeds/demo.sql` | محتوى تجريبي اختياري، بدون حسابات إدارة |
| `scripts/` | التثبيت والتشخيص والحسابات وخادم التطوير |
| `storage/` | جلسات وسجلات وملفات تشغيل خاصة |
| `tests/` | اختبارات حقيقية للطلبات والقاعدة والملفات |
| `docs/` و`.github/` | أدلة الفريق وقوالب Issues وPull Requests |

## 2. تثبيت الأدوات مرة واحدة

1. ثبت [Git for Windows](https://git-scm.com/install/windows)، وافتح PowerShell جديدا ثم نفذ `git --version`.
2. أنشئ حساب GitHub خاصا بك، وفعل التحقق بخطوتين. أرسل اسم حسابك فقط إلى القائد واقبل دعوة المستودع. لا تشارك كلمة المرور أو token.
3. ثبت [VS Code](https://code.visualstudio.com/download). افتح مجلد هذا المستودع وحده، ثم Terminal > New Terminal واختر PowerShell.
4. ثبت [XAMPP](https://www.apachefriends.org/download.html) الذي يوفر PHP 8.2 أو أحدث، مثلا في `C:\xampp`. شغل قاعدة البيانات من XAMPP Control Panel. عند استعمال خادم PHP أدناه، لا نحتاج تشغيل Apache. لا تحذف أو توقف تثبيت MySQL موجودا عندك بدون معرفة استعماله.
5. تأكد من نسخة القاعدة الحقيقية. النسخ المستهدفة في المشروع هي MySQL 8 أو MariaDB 10.6 فأحدث. بعض حزم XAMPP تحتوي MariaDB أقدم؛ الاختبارات المحلية السابقة مرت على 10.4.32، لكن هذا لا يعني اعتمادها للإنتاج. اتفق مع القائد قبل ترقية قاعدة فيها بيانات مهمة.
6. ثبت [Node.js LTS](https://nodejs.org/en/download) فقط إذا ستعدل وتبني CSS. ملف CSS الجاهز موجود، لذلك Node ليس ضروريا لتشغيل الموقع. تحقق بواسطة `node --version` و`npm --version`.

جهز PATH لهذه الجلسة في PowerShell:

```powershell
$env:Path = 'C:\xampp\php;C:\xampp\mysql\bin;' + $env:Path
php -v
php --ini
php -m
```

نحتاج إضافات PHP: `pdo_mysql` و`mbstring` و`fileinfo`، وكذلك `curl` للاختبارات. إذا غابت إضافة، عدل ملف `php.ini` الذي يظهر في `php --ini` ثم أعد تشغيل PHP. [مرجع إضافات PHP على Windows](https://www.php.net/manual/en/install.pecl.windows.php).

في إعدادات المستخدم `settings.json` داخل VS Code، يمكن ضبط المسار هكذا:

```json
{
  "php.validate.executablePath": "C:/xampp/php/php.exe",
  "php.validate.run": "onSave"
}
```

هذا يشغل [فحص PHP داخل VS Code](https://code.visualstudio.com/docs/languages/php)، ولا يعوض تثبيت PHP الحقيقي.

## 3. تنزيل المشروع بالطريقة المناسبة للعمل الجماعي

يفضل مجلد قابل للكتابة خارج Documents وDesktop وOneDrive، لأن حماية Windows قد تمنع PHP من الكتابة هناك:

```powershell
New-Item -ItemType Directory -Force "$env:LOCALAPPDATA\WikimediaMarocDev" | Out-Null
Set-Location "$env:LOCALAPPDATA\WikimediaMarocDev"
git clone https://github.com/Derdaki/Wikimedia_Morocco_Academy.git
cd Wikimedia_Morocco_Academy
code .
```

إذا لم يدمج القائد Pull Request الخاص بهذا الدليل بعد، جرب التغييرات من فرعه أولا:

```powershell
git fetch origin
git switch --track origin/codex/hackathon-onboarding
```

بعد الموافقة والدمج، تبدأ المهام الجديدة من `main` المحدث حسب دليل Git. لا تدمج فرع المعاينة بنفسك ولا تبدأ عملا يعتمد عليه بدون التنسيق مع القائد.

`clone` يجلب الملفات وتاريخ Git والفروع وارتباط `origin`. تحميل ZIP يجلب نسخة ملفات فقط، وليس بيئة مساهمة كاملة. داخل النسخة، `app/` و`public/` و`database/` موجودة مباشرة في جذر المستودع. لا تضف مجلدا خارجيا جديدا حول المشروع.

اضبط اسمك وبريدك لرسائل commit بواسطة `git config user.name` و`git config user.email` بقيمك الخاصة. يمكن استعمال بريد GitHub no-reply لحماية بريدك الشخصي.

## 4. إعداد البيئة المحلية وقاعدة البيانات

```powershell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
code .env
```

ضع `APP_URL=http://127.0.0.1:8085` و`DB_DATABASE=wikimedia_maroc` و`DB_HOST=127.0.0.1`. ضع منفذ واسم مستخدم وكلمة مرور قاعدة بيانات جهازك فقط. لا تفترض أن كلمة مرور root فارغة. المنفذ الافتراضي في المثال هو `3306`، لكن إعداد القائد الحالي يستعمل `3307`. لا تنسخ `.env` الخاص بشخص آخر.

استبدل المنفذ و`YOUR_LOCAL_DB_USER` بقيمك قبل تنفيذ:

```powershell
mysql -h 127.0.0.1 -P 3306 -u YOUR_LOCAL_DB_USER -p
```

الخيار `-p` يطلب كلمة المرور محليا، فلا تكتبها ضمن الأمر. داخل واجهة SQL:

```sql
SELECT VERSION();
CREATE DATABASE IF NOT EXISTS wikimedia_maroc
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

إذا لم تكن لك صلاحية إنشاء القاعدة، اطلب المساعدة من مسؤول قاعدة جهازك. يمكن استعمال phpMyAdmin إذا كان معدا عندك. لا تصل نسخة التطوير بقاعدة staging المشتركة عن طريق الخطأ.

ثم من مجلد المستودع:

```powershell
php scripts/migrate.php
# اختياري: للمحتوى التجريبي في قاعدة محلية جديدة فقط.
php scripts/seed.php
# حساب مسؤول أول خاص بقاعدة جهازك.
php scripts/create-admin.php
php scripts/doctor.php
```

المهاجرة تنشئ الجداول الناقصة، وليست نظاما كاملا لترقية كل تغييرات المخطط. خذ نسخة احتياطية قبل تعديل قاعدة مهمة. البذور تضيف أمثلة بدون تكرار slugs، لكنها لا تزامن بيانات الفريق. لا تشغلها عشوائيا على بيانات مهمة. لا يتم إنشاء مسؤول تلقائيا.

أمر إنشاء الحساب التفاعلي على Windows يفتح مساعد `scripts/admin-account.ps1` بإدخال كلمة مرور مخفي وتأكيدها. إذا لم يعمل اكتشاف الطرفية، استعمل مباشرة:

```powershell
powershell -NoProfile -File scripts/admin-account.ps1 -Action create
```

كلمة المرور يجب أن تكون من 10 إلى 72 **بايت UTF-8**؛ الحروف العربية قد تستعمل أكثر من بايت. لا تضع كلمة مرور في أمر محفوظ أو Issue أو لقطة شاشة أو محادثة. إذا منعت سياسة PowerShell الملف، راجعه مع القائد؛ لا تعطل حماية Windows بشكل عام.

يجب أن يعرض `doctor.php` نتيجة OK للإضافات والبيئة والقاعدة والجداول والروابط والكتابة في `public/uploads` و`storage/sessions`. اجعل `storage/logs` قابلا للكتابة أيضا. انتقال الجلسة إلى Temp لا يحل مشكلة رفع الصور. الحل المفضل لحماية Documents هو العمل من clone في مجلد قابل للكتابة، وليس تعطيل Defender أو إعطاء Everyone جميع الصلاحيات.

## 5. تشغيل الموقع الحقيقي

```powershell
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8085 -t public scripts/serve.php
```

اترك الطرفية مفتوحة وقاعدة البيانات شغالة:

- الموقع: <http://127.0.0.1:8085>
- تسجيل دخول الإدارة: <http://127.0.0.1:8085/admin/login>
- اللوحة بعد الدخول: `/admin`.
- تغيير كلمة مرورك: `/admin/account`، أي Mon compte / حسابي.

`localhost` و`127.0.0.1` يعنيان **جهازك أنت**. نفس الرابط عند زميلك يشير إلى جهازه، وليس إلى موقع مشترك. يمكن أن يستعمل الجميع 8085 على أجهزة مختلفة. إذا كان المنفذ مشغولا عندك، اختر منفذا آخر وعدل `APP_URL` بنفس القيمة. أوقف خادمك بواسطة Ctrl+C. مجلد الويب هو `public/` فقط، وليس جذر المستودع.

## 6. حساب GitHub ليس حساب لوحة الإدارة

حساب GitHub للمساهمة في **الكود**. حساب الإدارة موجود في جدول `users` للدخول إلى **لوحة PHP**. إدراج شخص في `team_members` لعرض سيرته لا ينشئ له حساب دخول.

في staging مشتركة، القائد وحده هو Super Administrator افتراضيا، ويضيف أربعة حسابات **Editor** بأسماء وبريد مستقل لكل شخص من صفحة Users / Utilisateurs. المحرر يدير المحتوى والملفات، ولا يدير المستخدمين أو الإعدادات الحساسة. طريقة الإنشاء والتسليم وتغيير كلمة المرور موضحة في [دليل الإدارة](ADMIN_GUIDE.md).

كل جهاز له قاعدة مستقلة. يمكنك إنشاء مسؤول محلي لتركيب نسخة جهازك، ثم حساب Editor لتجربة صلاحيات المساهم العادي. هذا لا يعطيك صلاحية Super Administrator في staging. إنشاء حساب عند القائد لا ينشئه عند باقي الأشخاص. Git push لا ينقل MySQL أو الحسابات أو الصور المرفوعة أو `.env`.

لا تضع جداول المستخدمين أو password hashes أو نسخ قواعد فيها بيانات حقيقية في Git. الموقع المشترك يحتاج إعداد staging مستقلا، HTTPS وقاعدة مشتركة وحسابات فردية ونسخا احتياطية. هذا الدليل لا ينشئ الاستضافة أو دعوات GitHub تلقائيا.

## 7. تعديل الكود واختباره

ابدأ بفرع خاص بمهمتك حسب [دليل Git](GIT_WORKFLOW.md). اتفق مع الفريق قبل تعديل ملف CSS أو layout مشترك.

إذا عدلت التصميم، عدل المصدر ثم ابن الملف النهائي:

```powershell
npm ci
npm run build:css
node --check public/assets/js/app.js
node --check tailwind.config.js
Get-ChildItem app,public,routes,scripts,tests -Filter *.php -Recurse -File |
  ForEach-Object { php -l $_.FullName; if ($LASTEXITCODE -ne 0) { throw 'PHP syntax failed' } }
```

لا تعدل CSS المبني وحده، ولا ترفع `node_modules`. افحص الهاتف والحاسوب والفرنسية والعربية واتجاه RTL والصور وأخطاء Console.

### الاختبارات تحتاج قاعدة منفصلة وطرفيتين

أنشئ قاعدة تجريبية فارغة اسمها `wikimedia_maroc_hackathon_test` بواسطة عميل SQL كما سبق. يجب أن ينتهي الاسم بـ`_test`. الاختبارات تضيف وتحذف سجلات وتغير إعدادات مؤقتا، لذلك لا تشغلها على `wikimedia_maroc` أو staging.

في **كلتا الطرفيتين** ومن نفس clone:

```powershell
$env:DB_DATABASE = 'wikimedia_maroc_hackathon_test'
$env:APP_URL = 'http://127.0.0.1:8086'
$env:SESSION_NAME = 'wmma_hackathon_test'
```

قيم الاتصال الأخرى تؤخذ من `.env` الخاص بك. في الطرفية A:

```powershell
php scripts/migrate.php
php scripts/seed.php
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8086 -t public scripts/serve.php
```

في الطرفية B:

```powershell
$env:TEST_BASE_URL = 'http://127.0.0.1:8086'
php tests/run.php
```

وعلى Windows، اختبر مساعد الحساب من الطرفية B أيضا:

```powershell
powershell -NoProfile -File tests/admin-console.ps1
```

هذا الاختبار يستعمل إدخالا آمنا محاكيا وحسابات مؤقتة للتأكد من نقل UTF-8 والإنشاء والاسترجاع. لا يعوض التأكد من إخفاء الكتابة فعليا في طرفية جهازك.

الطرفيتان وخادم HTTP يجب أن يستعملوا **نفس القاعدة التجريبية**. الاختبارات تنشئ حسابات مؤقتة وتجرب تسجيل الدخول والصلاحيات والمحتوى والرفع ثم تنظف بياناتها. سجل النتيجة الفعلية في PR، ولا تقل إن اختبارا نجح بدون تشغيله. أغلق طرفيتي الاختبار بعد الانتهاء حتى لا تبقى متغيرات بيئة توجه موقعك إلى القاعدة الخطأ؛ متغيرات البيئة تسبق `.env`.

## 8. مشاكل شائعة وحلولها

| المشكلة | ماذا نفعل؟ |
| --- | --- |
| فشل القاعدة أو HTTP 503 | تأكد أن MySQL/MariaDB يعمل وأن المنفذ والقاعدة والصلاحيات و`.env` صحيحة. شغل `doctor.php` ولا تنشر كلمة المرور. |
| PHP أو إضافة غير موجودة | افحص `Get-Command php` و`php --ini` و`php -m` ثم صحح PATH وملف الإعداد الحقيقي. |
| المنفذ مشغول | افحص `Get-NetTCPConnection -LocalPort 8085 -State Listen` واختر منفذا آخر. لا توقف برنامجا مجهولا. |
| فشل رفع الصور أو الجلسات | اعتمد نتيجة الكتابة الفعلية في doctor. استعمل clone خارج المجلد المحمي ولا تعطل Defender. |
| Git يرفض الرفع | اقبل دعوة المستودع، افحص `git remote -v`، واستعمل تسجيل Git Credential Manager في المتصفح. لا تضع token في الرابط. |
| تعارض merge | اتبع خطوات [GIT_WORKFLOW.md](GIT_WORKFLOW.md)، ونسق مع صاحب الملف بدل حذف عمله. |
| تعذر دخول الإدارة | تأكد من رابط وقاعدة الجهاز الصحيحين وحساب فعال. بعد محاولات فاشلة كثيرة انتظر 15 دقيقة. الجلسة تنتهي بعد ساعة بدون نشاط. راجع [ADMIN_GUIDE.md](ADMIN_GUIDE.md) للاسترجاع الآمن. |
| CSS أو صورة غائبة | تأكد من `APP_URL` ووجود الملفات ومن تشغيل الخادم بـ`-t public scripts/serve.php`. أعد بناء CSS فقط عند الحاجة ثم حدث الصفحة. |
| PowerShell يمنع مساعد الحساب | راجع الملف والسياسة مع القائد. لا تعوض الإدخال المخفي بكلمة مرور في سجل الأوامر. |

أكمل [قائمة الانضمام](ONBOARDING_CHECKLIST.md)، ثم افتح أول Pull Request صغير للمراجعة. الهدف أن تعمل باستقلالية، مع طلب المساعدة مبكرا عندما لا تفهم جزءا من الكود.
