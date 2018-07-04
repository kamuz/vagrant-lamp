Vagrant.configure("2") do |config|
  # Box Settings
  config.vm.box = "ubuntu/trusty64"
  # Network Settings
  config.vm.network "private_network", ip: "192.168.33.10"
  # Folder Settings
  config.vm.synced_folder ".", "/var/www/html"
  # Provider Settings
  config.vm.provider "virtualbox" do |vb|
    # Display the VirtualBox GUI when booting the machine
    vb.gui = false
    # Customize the amount of memory on the VM:
    vb.memory = "1024"
    vb.cpus = 2
  end
  # Provision Settings
  config.vm.provision "shell", path: "bootstrap.sh"
end