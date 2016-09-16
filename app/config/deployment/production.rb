server 'spformulier.pt-75.utr.surfcloud.nl', :app, :web, :primary => true

set :deploy_to,   "/www/support/data/"

default_run_options[:pty] = true
