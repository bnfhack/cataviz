# cataviz

PHP/SQLite Visualisations pour data.bnf.fr

## Installation sur un serveur PHP linux à travers SSH

Requis
* serveur Apache/PHP installé et fonctionnel, visible depuis l’internet, prenant en charge les .htaccess.


### Installation rapide

```bash
cd cataviz
# clone des sources github
# ne pas oublier le . à la fin pour ne pas créer de dossier enfant
git clone https://github.com/bnfhack/cataviz.git .
# installer les librairies PHP avec composer
composer u
# installer les modules Apache nécessaires
sudo a2enmod rewrite
# installer les extensions php nécessaires
sudo apt install php-sqlite3
# vérifier que Apache prend en compte les fichiers .htaccess
# directive AllowOverride All 
sudo vi /etc/apache2/sites-enabled/???.conf
        <Directory /var/www/html>
                Options Indexes FollowSymLinks
                AllowOverride All 
                Order allow,deny
                allow from all
        </Directory>

# redémarrer Apache pour que ces modifcations soient prises en compte
sudo service apache2 restart
# TODO, un lien pérenne pour télécharger la base de données SQLite

# en cas d’installation déportée
cd /var/www/html
sudo ln -s /data/cataviz/ cataviz
```



### Droits

Il peut arriver selon les serveurs que le dossier par défaut
du serveur Apache `/var/www/html` soit volontairement limité par l’administrateur
afin d’isoler les applications sur un disque différent, par exemple plus rapide en lecture. Voyez par exemple ce serveur, il y a bien plus de place sur sur le dossier `/data` (69G) alors que le dossier racine `/` est déjà plein à 33% et peut s’encrasser assez vite avec les mois et les années.

```bash
df -h
Type   Size  Used Avail Use% Mounted on
tmpfs  1.6G  816K  1.6G   1% /run
ext4    47G   15G   31G  33% /
tmpfs  7.9G   28K  7.9G   1% /dev/shm
tmpfs  5.0M     0  5.0M   0% /run/lock
ext2   494M  173M  297M  37% /boot
nfs     72G  3.1G   69G   5% /data
```

Pour un tel dossier `/data`, il est de bonne pratique pour la sécurité que l’utilisateur qui installe une aplication appartienne à un groupe (ex : `devs`), avec les autres utilisateurs pouvant intervenir sur cette application, sans passer par les droits du super-utilisateur (`sudo`, `su`…). Il faut que les `devs` puissent lirent et écrire librement dans les applcations.

Par sécurité, le dossier parent des applications doit rester propriété de `root:root`, afin qu’y ouvrir un nouveau dossier reste une opération rare. Tous les dossiers à l’intérieur doivent appartenir au groupe des `:devs` (l’utilisateur importe peu). Il faut assurer que tous les fichiers et dossiers créés par les `devs` donnent les droits de lire et surtout écrire aux autres `devs`, mais pas à tous. Deux trucs :

* vérifier que le `umask` par défaut des `devs` donne les droits d’écriture sur les dossiers créés `=0002` (plusieurs politiques possibles, globales ou pour chaque utilisateur)
* vérifier que les dossiers créés héritent du groupe parent, voyez le peit `s` dans `drwxrwsr-x`.

```bash
umask
0002
sudo mkdir cataviz
sudo chmod g+ws cataviz
sudo chown me:devs cataviz
ls -alh
drwxr-sr-x  3 root root 4.0K Jan 25 15:56 .
drwxr-xr-x 21 root root 4.0K Jan 23 21:28 ..
drwxrwsr-x  2 me   devs 4.0K Jan 25 15:58 cataviz
```

