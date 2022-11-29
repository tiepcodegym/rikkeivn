Deployer for Intranet
===

### Requirement
1. PHP >= 7.0
2. Deployer 6.3


### Configuration
- copy hosts.yml.example to hosts.yml
- Edit file hosts.yml, change information of server hostname, user, port, identityFile, branch, deploy_path.
- Create file config/key.pem. This is pem key to remote ssh to server = identityFile in hosts.yml file

### Struct
1. deployer file: deployer.phar 6.3.0
2. deploy.php file: file declare task custom

### Task run
1. `php deployer deploy rikkei.vn`
    

    - php: enviroment
    - deployer: file deployer, phar version 6.3.0
    - deploy: task `deploy`
    - rikkei.vn: `stage`, config in hosts.yml


2. `php deployer seed rikkei.vn`

### Step run default
1. deploy:prepare
2. deploy:lock
3. deploy:release : create folder release
4. deploy:update_code : clone code
5. deploy:shared : create folder share, storage
6. deploy:vendors : composer install
7. deploy:writable
8. artisan:storage:link
9. artisan:view:clear
10. artisan:cache:clear
11. artisan:config:clear
12. artisan:optimize
13. migrate
14. deploy:symlink
15. artisan:config:cache
16. artisan:cache:clear
17. deploy:unlock
18. cleanup
