Core package api subscriber notify
===

Api Subscriber email and notify
 
[x] Subscriber email supplement

[x] Subscriber email leave day

Features
---
1.Subscriber email supplement

    uri: /subscriber_notify/supplement/:empploye_id/:type
        empploye_id: Mã nhân viên đăng ký
        type: Loại email thông báo
            1: Đăng ký or edit
            2: Duyệt đơn
            3: Từ chối
    Headers params:
        Bearer Token: Lấy trong config "/config/api.php" (token)
        Content-Type: 'application/json'
    Method: POST
    Body params:
        id: Mã đăng ký bổ sung công
    Response 
        success:
            0 - Failed
            1 - Is Successfuly
            
2.Subscrible email leave day
    
    uri: /subscriber_notify/leave-day/:empploye_id/:type
        empploye_id: Mã nhân viên đăng ký
        type: Loại email thông báo
            1: Đăng ký or edit
            2: Duyệt đơn
            3: Từ chối
    Headers params:
        Bearer Token: Lấy trong config "/config/api.php" (token)
        Content-Type: 'application/json'
    Method: POST
    Body params:
        leaveday_id: Mã đăng ký leave day
    Response 
        success:
            0 - Failed
            1 - Is Successfuly
            
