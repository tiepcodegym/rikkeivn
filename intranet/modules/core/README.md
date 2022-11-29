Core package
===

This is a package for Rikkei Intranet System

Features
---

- [x] Authentication with Google
- [ ] Access control
- [ ] Enable / disable feature
- [x] Integrate AdminLTE Theme
- [x] Allow config menu by file
- [x] Integrate switch localization

#### migration and seed
php artisan vendor:publish --tag=database
php artisan migrate
php artisan db:seed

#### Recuirement demo data
Add demo data for recruitment
Fill phone field demo in profile data, presenter autoload:

    0912345678
    0922345678
    0932345678
    0942345678

#### Acl
Acl has 3 level:
- level 1: acl group, ex: Profile, Recruitment, Setting, ....
- level 2: action label, ex: View profile, View list member,...
- level 3: action route, ex: team::setting.team.edit, team::team.member.edit

#### Add domain allow logged
edit file `config.domain_logged.php`

#### delete confirm modal
- Button click has class `delete-confirm`
- option: 
    + data-noti: text show body modal, default text is "Are you sure delete item?"

#### Seed db
- Seed acl: `php artisan db:seed --class=AclSeeder`
- Seed email from excel: `php artisan db:seed --class=ImportEmailSeeder`

#### Environment
Edit file `.env`, field `APP_ENV` has value `local` OR `production`

#### Link file assets
- use `Rikkei\Core\View\CoreUrl::asset('pathFile')` to get link file assets
- Benefit: refresh cache browser