<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'UF Learn 6');

// Project repository
set('repository', 'https://github.com/userfrosting/learn.git');
set('branch', 'main');

// Writable dirs by web server 
set('writable_dirs', [
    'app/cache',
    'app/logs',
    'app/sessions',
]);
set('allow_anonymous_stats', false);

// Hosts
host('prod')
    ->set('remote_user', 'deploy-learn')
    ->set('hostname', '157.245.12.207')
    ->set('deploy_path', '/var/www/learn6');

// UF Bakery task
task('bakery:bake', function () {
    within('{{release_path}}', function () {
        run('php bakery bake');
    });
});
task('bakery:setenv', function () {
    within('{{release_path}}', function () {
        run('php bakery setup:env --mode=production');
    });
});

// Tasks
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'bakery:setenv',
    'bakery:bake',
    'deploy:symlink',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
