<?php
namespace Deployer;

require 'recipe/laravel.php';

/**
 * set variable
 */
// Project name
set('application', 'intranet');

// Project repository
set('repository', 'git@git.rikkei.org:production/intranet.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false); 
set('keep_releases', 4);
set('default_stage', 'production');
set('default_timeout', 1800);

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', [
    'bootstrap/cache'
]);
    
// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});
//-- declare function task --//

// init copy .env default
task('copyEnv', function () {
    run('cp -n {{deploy_path}}/release/.env.example {{deploy_path}}/shared/.env');
});

// public storage link
task('public:link', function () {
    run('ln -s {{deploy_path}}/shared/storage/app/public/ {{release_path}}/public/storage');
});

// migration
task('migrate', function () {
    run('php {{release_path}}/artisan migrate --force');
});

// seed
task('seed', function () {
    run('php {{release_path}}/artisan db:seed --force');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
after ('artisan:storage:link', 'copyEnv');
// Migrate database before symlink new release.
before('deploy:symlink', 'migrate');
after('migrate', 'seed');
after('deploy:symlink', 'artisan:config:cache');
after('deploy:symlink', 'artisan:cache:clear');
after('artisan:storage:link', 'public:link');
// include hosts
inventory('hosts.yml');
