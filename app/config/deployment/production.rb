server 'support.surfconext.nl', :app, :web, :primary => true

default_run_options[:pty] = true

before "deploy:create_symlink" do
    custom.set_permissions
end

namespace :custom do
    task :set_permissions, :roles => :app, :except => { :no_release => true } do
        run "#{try_sudo} /usr/local/sbin/fix-www-permissions.sh /www/support/"
    end
end
