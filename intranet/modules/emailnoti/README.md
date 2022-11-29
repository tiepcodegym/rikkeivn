Team package
===

This is a package for Rikkei Intranet System

Features
---

- [ ] Manage teams

#### Change acl
Edit file `config/acl.php`

#### use Acl
- class `Rikkei\Team\View\Permission`
- Get Singleton: `Permission::getInstance()`
- Check action allow: `Permission::isAllow()`
- Check scope none: `Permission::isScopeNone()`
- Check scope self: `Permission::isScopeSelf()`
- Check scope team: `Permission::isScopeTeam()`
- Check scope company: `Permission::isScopeCompany()`
- test accout bod: add line into file `.env`: ACCOUNT_ROOT=giangnt2@rikkeisoft.com