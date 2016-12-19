This is the Student Guide Drupal distribution.

# Get Started

Requirements:
  * [Composer](https://getcomposer.org)
  * [Wundertools](https://github.com/wunderkraut/wundertools)

Steps for setting up environment:
* Create your project infrastructure repository using
  [Wundertools](https://github.com/wunderkraut/wundertools) as template
* Specify your roles that you want to use, exampel for `vagrant.yml`:
```yaml
- hosts: default
  become: true
  become_method: sudo
  user: vagrant
  roles:
   - { role: base, tags: [ 'base' ] }
   - { role: php-fpm, tags: [ 'php-fpm' ] }
   - { role: nginx, tags: [ 'nginx' ] }
   - { role: varnish, tags: [ 'varnish' ] }
   - { role: memcached, tags: [ 'memcached' ] }
   - { role: drush, tags: [ 'drush' ] }
   - { role: drupal-console, tags: ['drupal-console']}
   - { role: dbserver, tags: [ 'dbserver' ] }
   - { role: drupal-db, tags: [ 'drupal-db' ] }
   - { role: selfencrypt, tags: [ 'selfencrypt' ] }
   - { role: sslterminator, tags: [ 'sslterminator' ] }
```
* Configure your `site.yml`, example for `local`
```yaml
local:
  link:
    - student_guide/composer.json: composer.json
    - student_guide/composer.lock: composer.lock
    - student_guide: web/profiles/student_guide
    - files: web/sites/default/files

  copy:
    - conf/services.yml: web/sites/default/services.yml
    - conf/settings.php: web/sites/default/settings.php
    - conf/settings.local.php: web/sites/default/settings.local.php
    - conf/_ping.php: web/_ping.php
```
* Configure your `commands.yml`, example:
```yaml
# Basic new site functionality
new:
  - verify: "Type yes to verify you want to build a new installation: "
  - shell: git clone https://github.com/UH-StudentServices/student_guide.git
  - make
  - backup
  - shell: chmod -R a+w current
  - purge
  - finalize
  - install
  - cleanup
  - shell: chmod -R a-w current

# Basic site development (this allows you to modify contents of current
# directory that is required for composer require) 
dev:
  - shell: cd student_guide && git remote set-url origin git@github.com:UH-StudentServices/student_guide.git
  - shell: chmod -R a+w current

# Basic site update functionality
update:
  - make
  - backup
  - shell: chmod -R a+w current
  - finalize
  - update
  - cleanup
  - shell: chmod -R a-w current

reinstall:
  - shell: cd current/web && drush sql-drop -y
  - install
```
* Setup the local vagrant environment

```bash
$ vagrant up
```

* Setup Drupal
```bash
$ vagrant ssh
$ cd /vagrant/drupal
$ mkdir files
$ ./build.sh new
```
* Optionally if you plan to do development, run also:
```bash
$ ./build.sh dev
```

# Common development tasks reference

All following commands requires `./build.sh dev` command to work (that is
configured above).

Getting a new contrib module:
```bash
[vagrant@local ~]$ cd /vagrant/drupal/current
[vagrant@local ~]$ composer require drupal/pathauto
```

@TODO More to come :)
