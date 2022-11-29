RikkeiSoft's Intranet System
===

This is Intranet system of RikkeiSoft Co. Ltd.,   

Requirement
---
1. PHP 5.6 hoặc 7.0
2. Mysql or mariadb
3. Apache or nginx
4. Redis

Development
---

1. Clone source code

    ```
    $ git clone git@gitlab.rikkei.org:production/intranet.git /path/to/project/source
    ```

2. setup domain, vhost cho dự án (lên mạng search cách tạo vhost cho apache hoặc nginx). ví dụ domain: rikkei.sd`

3. edit host file trỏ domain vhost về ip local

4. tạo symlink cho thư mục upload file storage
Trên linux: ln -s /path/to/project/storage/app/public/ /path/to/project/public/storage
Trên window: mklink /D /path/to/project/public/storage /path/to/project/storage/app/public

5. download các file dependency
composer install

6. Copy file .env.examle tới .env

7. render key
php artisan key:generate

8. Thay đổi thông tin cần thiết trong file .env: database, mail.

9. ở bước login. Hiện tại đang login qua gmail. Nếu trên local ko dùng https thì sẽ ko callback đc. Fix bằng cách vào file modules\core\src\Http\Controllers\PagesController.php và edit thêm các dòng bôi vàng như ảnh ở link dưới:
https://drive.google.com/file/d/1m0Wi_P5sJK3O7z6ioa639O8hy2qkkaG_/view?usp=sharing

Sau đó login bằng cách gõ rikkei.sd?email={email}

Lưu ý: Email login cần có trong bảng employees và bảng users


10. Thay đổi account root: account có full quyền trong hệ thống
ACCOUNT_ROOT=xxx@rikkeisoft.com

11. remove cache
php artisan config:cache

12. Setup crontab
    - * * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
    - run project report
    - run email queue
    - run get loc of gitlab
    - ...


13. minify js
    - use `gulp --production`, `npm run prod-watch`, `npm run prod`
    - `gulp angular` for angular file, argument `--dev` to not minify js file
    - Các file trong gulp.js bị comment lại để tránh lúc dev dịch nhiều, dịch lâu.
    - Các file của 1 module được xếp gần nhau, khi dùng thì xóa bỏ comment để dịch, code xong phần module đấy lại comment các file asset vào.

14. Seeder
- Mỗi module có 1 file seeder chung, có tiền tố A để dễ theo dõi. Seeder này sẽ call
các seeder khác trong cùng 1 module. Seeder này được khai báo trong `/databases/seeds/DatabaseSeeder.php`
(Tham khảo module core để làm theo).
Ví dụ gọi seeder của module core trong DatabaseSeeder: `$this->call(\Rikkei\Core\Seeds\ACoreSC::class);`
- Trong từng module, các file seeder đã bị comment để tránh lúc seeder, gọi nhiều seeder gây chậm.
Khi mới init dự án, bỏ hết comment để seed dữ liệu mẫu, quan trong nhất là seed ở module core và module team.
Nếu dùng cái nào, bỏ comment của nó đi và push lên master.
Khi được merge master, sau 1 thời gian, nên comment seeder đó lại.
- Trong từng seeder, sử hàm `checkExistsSeed` tham số version của seeder là 1 số,
để check xem seed đó đó được sử dụng chưa, nếu sử dụng rồi thì không chạy các code tiếp theo nữa.
Tăng version để chạy lại seeder đó.

15. Cron schedule:run
- Gọi file core/src/Console/Kerel.php
- Trong file chính gọi các Kernel của các module khác. Ví dụ `\Rikkei\ManageTime\Console\TimeKernel::call($schedule);`
- Trong Kernel của từng module, khai báo schedule call như thường. Ví dụ

    ```php
    try {
        $schedule->call(function () {
            Email::sent();
        })->cron('* * * * *');
    } catch (Exception $ex) {
        Log::info($ex);
    }
    ```


Modules
---
Có 12 modules được chia theo nhóm các chức năng. 

STT | Module | Chức năng
--- | ------ | ---------
01 | [Core][md-core] | Cơ bản: authenticate, access control, enable/disable maintenance mode
02 | [Accounting][md-accounting] | nghiệp vự kế toán, xuất bảng lương
03 | [Assets][md-assets] | quản lý tài sản, order mượn thiết bị, booking phòng họp
04 | [Customer][md-customer] | quản lý thông tin khách hàng
05 | [Employee][md-employee] | quản lý nhân viên, các chức năng của mỗi nhân viên
06 | [Music][md-music] | Order phát nhạc và quản lý phát nhac theo yêu cầu
07 | [NEWS][md-news] | Quản lý bảng tin, các quy định, tin tức nội bộ
08 | [Project][md-project] | Quản lý thông tin dự án, CSS của dự án
09 | [Team][md-team] | Quản lý team
10 | [Training][md-training] | Quản lý các hoạt động đào tạo, kiểm tra nhân viên định kỳ về ISMS, ...
11 | [Working][md-working] | Quản lý việc chấm công, là thêm giờ
12 | [Recruitment][md-recruitment] | Phần tuyển dụng và kiểm tra ứng viên

Mỗi module được viết như một package và có cấu trúc như sau:

```
module_name
|-- composer.json
|-- config
|   |-- other_config.php
|   `-- routes.php
|-- README.md
|-- resources
|   |-- lang
|   `-- views
|-- src
|   |-- Console
|   |-- Events
|   |-- Exceptions
|   |-- Http
|   |   |-- Controllers
|   |   |-- Middleware
|   |   `-- Requests
|   |-- Jobs
|   |-- Listeners
|   |-- Model
|   |-- Policies
|   |-- Providers
|   `-- ServiceProvider.php
`-- tests
```

Trong đó:

- File **composer.json** chứa thông tin định nghĩa về package theo định dạng của **Composer**.
  Khi một module yêu cầu 1 package nào đấy thì mọi người nên cập nhật vào file này và file `composer.json` 
  ở thư mục gốc của project

- Thư mục **config** chứa các file cấu hình cho module như `routes.php`, ... 
  Chúng sẽ được chỉ định load bởi các class Provider của bạn. Xem class `Rikkei\Core\Providers\RouteServiceProvider`
  để thấy cách load file `config/routes.php`

- File **README.md** mô tả về module: chức năng, ...
- Thư mục **resource** chứa các resource của module như view scripts, language files, sass, ...
  Xem class `Rikkei\Core\ServiceProvider` để thấy cách load view scripts

- Thư mục **src** chứa các class của module: controller, model, policie, provider, ... 
  Mỗi module luôn có 1 class `ServiceProvider` để load module đó

- Thư mục **tests** chứa nội dung unit tests của module


[md-core]:         ./modules/core/README.md
[md-accounting]:   ./modules/accounting/README.md
[md-assets]:       ./modules/assets/README.md
[md-customer]:     ./modules/customer/README.md
[md-employee]:     ./modules/employee/README.md
[md-music]:        ./modules/music/README.md
[md-news]:         ./modules/news/README.md
[md-project]:      ./modules/project/README.md
[md-recruitment]:  ./modules/recruitment/README.md
[md-team]:         ./modules/team/README.md
[md-training]:     ./modules/training/README.md
[md-working]:      ./modules/working/README.md

