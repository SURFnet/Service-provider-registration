set :application, "SURFnet Service Provider Registration"
set :domain,      "support.surfconext.nl"
set :deploy_to,   "/www/support/data/"
set :app_path,    "app"

set :repository, "git@github.com:SURFnet/Service-provider-registration.git"
set :scm,        :git
set :branch,     "release"

role :web, domain
role :app, domain, :primary => true

ssh_options[:forward_agent] = true
set :user,                "bas"
set :use_sudo,            false
set :webserver_user,      "support_surfconext"

set :permission_method,   :acl
set :use_set_permissions, true
set :writable_dirs,       ["app/cache", "app/logs", "app/data"]

set :keep_releases, 3

set :deploy_via, :remote_cache

set :shared_files,    ["app/config/parameters.yml"]
set :shared_children, [app_path + "/logs", app_path + "/data", web_path + "/uploads"]

set :use_composer, true
set :copy_vendors, true

set :dump_assetic_assets,   false
set :update_assets_version, true

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

# Run migrations before warming the cache
before "symfony:cache:warmup", "symfony:doctrine:schema:update"

# Update translations
namespace :symfony do
  desc "Updates translations"
  task :update_translations, :roles => :app, :except => { :no_release => true } do
    stream "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} lexik:translations:import -g -c #{console_options}'"
  end
end

after "symfony:doctrine:schema:update", "symfony:update_translations"

# Clean old releases after deploy
after "deploy", "deploy:cleanup"
