<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'UF Learn');

// Project repository
set('repository', 'https://github.com/userfrosting/learn.git');
set('branch', 'website');
set('update_code_strategy', 'clone'); // Required for submodules

// Will be effective with Deployer 7.0
set('current_path', '{{deploy_path}}/user');

// Writable dirs by web server 
set('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts
host('prod')
    ->set('remote_user', 'deploy-learn')
    ->set('hostname', '157.245.12.207')
    ->set('deploy_path', '/var/www/learn-stage');

// Grav task
task('grav:install', function () {
    within('{{deploy_path}}', function () {
        run('bin/grav install');
    });
});
task('grav:clean', function () {
    within('{{deploy_path}}', function () {
        run('bin/grav clean');
    });
});
task('grav:clearcache', function () {
    within('{{deploy_path}}', function () {
        run('bin/grav clearcache');
    });
});

// Git submodule task
task('git:submodule', function () {
    within('{{release_path}}', function () {
        run('git submodule update --init');
    });
});

// Tasks
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:clear_paths',
    'git:submodule',
    'deploy:symlink',
    'grav:install',
    'grav:clean',
    'grav:clearcache',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
