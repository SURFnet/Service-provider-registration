set :stages,        %w(production staging development)
set :default_stage, "development"
set :stage_dir,     "app/config/deployment"
require 'capistrano/ext/multistage'

set :application, "SURFnet Service Provider Registration"
set :app_path,    "app"

set :repository, "git@github.com:SURFnet/Service-provider-registration.git"
set :scm,        :git
set :branch,     "release"

ssh_options[:forward_agent] = true
set :user,                "boy"
set :use_sudo,            false
set :webserver_user,      "apache"

set :permission_method,   :acl
set :use_set_permissions, true
set :writable_dirs,       ["app/cache", "app/logs", "app/data", web_path + "/img/logos"]

set :keep_releases, 999

set :deploy_via, :remote_cache

set :shared_files,    ["app/config/parameters.yml"]
set :shared_children, [app_path + "/logs", app_path + "/data", web_path + "/uploads", web_path + "/img/logos"]

set :use_composer, true
set :copy_vendors, false

set :dump_assetic_assets,   false
set :update_assets_version, true

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

# Run migrations before warming the cache
before "symfony:cache:warmup", "symfony:doctrine:schema:update"
after "symfony:doctrine:schema:update", "deploy:migrate"

# Update translations
namespace :symfony do
  desc "Updates translations"
  task :update_translations, :roles => :app, :except => { :no_release => true } do
    stream "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} lexik:translations:import -g -c #{console_options}'"
  end
end

after "symfony:doctrine:schema:update", "symfony:update_translations"

# Update templates
namespace :symfony do
  desc "Updates templates"
  task :update_templates, :roles => :app, :except => { :no_release => true } do
    stream "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} app:templates:import #{console_options}'"
  end
end

after "symfony:update_translations", "symfony:update_templates"

# Flush memcached
namespace :memcached do
  desc 'Flushes whole memcached local instance'
  task :flush do
    stream "perl -MIO::Socket::INET -e '$sock = IO::Socket::INET->new(\"localhost:11211\"); $sock->write(\"flush_all\n\"); print $sock->getline(); $sock->close();'"
  end
end

after "deploy:update", "memcached:flush"

namespace :symfony do
  desc "Clear accelerator cache"
  task :clear_accelerator_cache do
    capifony_pretty_print "--> Clear accelerator cache"
    run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} cache:accelerator:clear --cli #{console_options}'"
    capifony_puts_ok
  end
end

# clear accelerator cache
after "deploy", "symfony:clear_accelerator_cache"
after "deploy:rollback:cleanup", "symfony:clear_accelerator_cache"

# Clean old releases after deploy
# Don't: will do this manually, if required
#after "deploy", "deploy:cleanup"
