# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "smallhadroncollider/centos-6.4-lamp"
  config.vm.hostname = "dev.support.surfconext.nl"
  config.hostsupdater.aliases = ["serviceregistry.dev.support.surfconext.nl"]
  config.vm.network "private_network", ip: "192.168.33.19"
  config.vm.synced_folder ".", "/vagrant"
  config.ssh.forward_agent = true

  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1536"]
  end

  config.vm.provision "shell", path: "provisioning/development.sh"
end
