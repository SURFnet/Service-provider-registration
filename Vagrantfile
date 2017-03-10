Vagrant.configure(2) do |config|
  config.vm.box = "centos/7"

  config.vm.network "private_network", ip: "192.168.33.19"
  config.vm.hostname = "dev.support.surfconext.nl"
  config.hostsupdater.aliases = ["serviceregistry.dev.support.surfconext.nl"]

  config.vm.synced_folder ".", "/vagrant", type: "virtualbox"

  config.vm.provision "ansible" do |ansible|
    ansible.playbook = "ansible/vagrant.yml"
    ansible.groups = {"dev" => "default"}
  end
end
