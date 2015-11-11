server 'surf.dev', :app, :web, :primary => true

set :deploy_to,   "/var/www/spform/"

set :user,           "vagrant"
set :webserver_user, "apache"
