# Freescout Following Module
FreeScout Module that adds a "Following" folder showing conversations followed by the user.

<img src="Public/img/freescout-following-module-256x256.png" width="192" height="192" style="border-radius: 1em;" />

## Screenshot

![Following Folder](Public/img/FreeScout-Following-Folder.png)

## Install
1. Navigate to your Modules folder e.g. `cd /var/www/html/Modules`
2. Run `git clone https://github.com/avenjamin/freescout-Following-Module.git Following`
3. Run `chown -R www-data:www-data Following` (or whichever user:group your webserver uses)
4. Activate the Module in the FreeScout Manage > Modules menu.

## Update
1. Navigate to the Following Module folder e.g. `cd /var/www/html/Modules/Following`
2. Run `git pull`
3. Run `chown -R www-data:www-data .` (or whichever user:group your webserver uses)
4. Enjoy the update!

## Known issues:
* Active conversations count doesn't update properly

<a href="https://www.buymeacoffee.com/benperry" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/default-orange.png" alt="Buy Me A Coffee" height="41" width="174"></a>
