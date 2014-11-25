set :application, "SURFnet Service Provider Registration"
set :domain,      "vps20.ibuildings.com"
set :deploy_to,   "/var/www/surfform"
set :app_path,    "app"

set :repository, "git@github.com:SURFnet/Service-provider-registration.git"
set :scm,        :git

role :web, domain
role :app, domain, :primary => true

ssh_options[:forward_agent] = true
set :user,                "root"
set :use_sudo,            false

set :permission_method,   :acl
set :use_set_permissions, true
set :writable_dirs,       ["app/cache", "app/logs", "app/data"]

set :keep_releases, 3

set :deploy_via, :remote_cache

set :shared_files,    ["app/config/parameters.yml"]
set :shared_children, [app_path + "/logs", app_path + "/data", web_path + "/uploads"]

set :use_composer, true
set :copy_vendors, true

set :dump_assetic_assets,   true
set :update_assets_version, true

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

# Run migrations before warming the cache
before "symfony:cache:warmup", "symfony:doctrine:schema:update"

# Clean old releases after deploy
after "deploy", "deploy:cleanup"
