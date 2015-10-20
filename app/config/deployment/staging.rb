server 'imogen.surfnet.nl', :app, :web, :primary => true

set :webserver_user, "www-data"
set :deploy_to,   "/srv/spform-staging/"

default_run_options[:pty] = true

