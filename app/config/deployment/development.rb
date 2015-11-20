server 'surf.dev', :app, :web, :primary => true

set :deploy_to,   "/var/www/spform/"

set :user,           "vagrant"
set :webserver_user, "apache"
set :symfony_env_prod, "dev"

set :composer_options,  "--verbose --prefer-dist --no-progress"
