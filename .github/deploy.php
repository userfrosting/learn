<?php
namespace Deployer;

require 'recipe/common.php';

// Project name
set('application', 'UF Learn 6');

// Project repository
set('repository', 'https://github.com/userfrosting/learn.git');
set('branch', 'main');
set('update_code_strategy', 'clone'); // Required for submodules

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
    ->set('deploy_path', '/var/www/learn6')
    ->set('identity_file', '~/.ssh/deploy_rsa');

// UF Bakery task
task('bakery:bake', function () {
    within('{{release_path}}', function () {
        run('php bakery bake');
    });
});
task('composer:update', function () {
    within('{{release_path}}', function () {
        run('composer update --no-dev -o');
    });
});
task('bakery:setenv', function () {
    within('{{release_path}}', function () {
        run('php bakery setup:env --mode=production');
    });
});

// Git submodule task
task('npm:update', function () {
    within('{{release_path}}', function () {
        run('npm update');
    });
});

// Tasks
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:clear_paths',
    'composer:update',
    'npm:update',
    'bakery:setenv',
    'bakery:bake',
    'deploy:symlink',
    'deploy:unlock',
    'deploy:cleanup',
    'deploy:success'
]);

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
