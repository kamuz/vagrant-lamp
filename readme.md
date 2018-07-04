## Vagrant

**Vagrant** позволяет автоматизировать процес создания рабочего окружения на базе Linux с использованием средств виртуализации, например VirtualBox. Можно было бы не использовать Vagrant, а делать всё вручную, но это займёт намного больше времени. С Vagrant вам понадобится поместить файл конфига в папку с проектом и поднять бокс. То есть вместо настройки окружения за 3-4 часа примерно, вы его настраиваете в течении 1 минуты.

Самый долгий процесс - это скачивание самой ОС, если её нет то она загрузиться автматически во время первого поднятия.

После установки VirtualBox и Vagrant, в первую очередь нужно инициализировать Vagrant в нужной вам папке:

```
vagrant init ubuntu/trusty64
```

`ubuntu/trusty64` - это чистая ОС Ubuntu Server 16.04 LTS. Можно поставить сборку с уже предустановленным ПО. Полный список можно найти на странице - [https://app.vagrantup.com/boxes/search](https://app.vagrantup.com/boxes/search).

Это сгенерирует Vagran файл, которым мы можем настроить, чтобы после запуска бокса мы получили готовое окружение.

Можно сразу же запустить систему командой - `vagrant up`, делать это нужно в папке с файлом *Vagrant*.

Заходим в систему командой `vagrant ssh` и поставим Git и Apache:

```bash
sudo apt-get update
sudo apt-get install -y git
sudo apt-get install -y apache2
ls /var/www
```

`guest` - это ваш бокс, `host` эта ваш компьютер.

После установки Apache вы можете получить доступ вашей папке `var/www/html` через `127.0.0.1:8080` или через IP адрес `192.168.33.10` - в зависимости от того какой параметр вы раскомментировали в настройках. Выберем второй вариант.

Теперь чтобы мы могли обращаться к нашему проекту по более понятному URL нам нужно поправить файл `host`, который на Mac находиться по адресу `/etc/hosts`, а на Windows `c:\Windows\System32\drivers\etc\` при этом у вас должны быть права суперадминистратора:

```
192.168.33.10 mysite.loc
```

Теперь мы можем обратиться по этому адресу.

Чтобы мы могли вносить правки в локальные папки на нашем компьютере нам нужно настроить общий доступ, для этого в файле конфигурации пропишем:

```ruby
config.vm.synced_folder ".", "/var/www/html"
```

Таким образом папка с нашим проектом, где мы инициализировали Vagrant и папка на нашем сервере будут синхронизироваться между собой.

Команда `vagrant destroy` не удалит Vagrant файл, а только бокс.

Итоговый файл у нас будет выглядеть так:

*Vagrantfile*

```ruby
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
```

Раздел с обеспечением `config.vm.provision` позволяет указать какой софт нам нужно поставить во время инициализации бокса. Мы создадим для этого дела отдельный файл, чтобы не писать эти команды инлайн.

*bootstrap.sh*

```bash
# Update Packages
apt-get update

# Upgrade Packages
apt-get upgrade

# Basic Linux Stuff
apt-get install -y git

# Apache
apt-get install -y apache2

# Enable Apache Mods
a2enmod rewrite

#Add Onrej PPA Repo
apt-add-repository ppa:ondrej/php
apt-get update

# Install PHP
apt-get install -y php7.2

# PHP Apache Mod
apt-get install -y libapache2-mod-php7.2

# Restart Apache
service apache2 restart

# PHP Mods
apt-get install -y php7.2-common
apt-get install -y php7.2-mcrypt
apt-get install -y php7.2-zip

# Set MySQL Pass
debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'

# Install MySQL
apt-get install -y mysql-server

# PHP-MYSQL lib
apt-get install -y php7.2-mysql

# Restart Apache
sudo service apache2 restart
```

После запуска `vagrant up` создадим БД в MySQL и добавим пару строк:

```sql
mysql -u root -p
CREATE DATABASE test;
USE test;
CREATE TABLE posts(id INT AUTO_INCREMENT, text VARCHAR(255) NOT NULL, PRIMARY KEY(id));
INSERT INTO posts (text) VALUES('Hello, world');
INSERT INTO posts (text) VALUES('Test blog post');
SELECT * FROM posts;
quit;
exit
```

Создаём файл:

*/var/www/html/index.php*

```php
<?php
$dbhost = 'localhost:3306';
$dbuser = 'root';
$dbpass = 'root';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);

if(!$conn){
    die('Could not connect: ' . mysqli_error());
}
echo 'Connect successfully';
mysqli_select_db($conn, 'test');
$query = 'SELECT * FROM posts';
$result = mysqli_query($conn, $query);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hello, world!</title>
</head>
<body>
    <h1>Welcome to Vagrant</h1>
    <?php if(mysqli_num_rows($result) > 0): ?>
        <ul>
            <?php while($row = mysqli_fetch_object($result)): ?>
                <li><?php echo $row->text; ?></li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No posts</p>
    <?php endif; ?>
    <?php //phpinfo(); ?>
</body>
</html>
```

Тестим результат.