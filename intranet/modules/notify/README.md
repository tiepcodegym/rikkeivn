Notify package
===

This is a package for Rikkei Intranet System

Features
---

- [ ] Rikkei Notify
- [ ] Publish and store regulations

Guide
---
- \RkNotify::put(
    array/integer reciever employee id,
    content,
    link,
    [
        'actor_id' => xx,
        'schedule_code' => 'yy',
        'category_id' => zz,
        'icon' => 'icon.png',
    ]
);
- actor_id: employee id or current user id
- schedule_code: unique value (custom define) for cronjob notification
