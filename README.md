# DUMB forum files

This repository hosts forum files for the Deltarune Unofficial Message Boards revival. All commits to this repository are automatically synchronized to the server hosting the forum.

## Installation

If you want to contribute to this repository, you might want to set up a local instance of the DUMB to run on your computer. (If you're contributing exclusively JavaScript and CSS you may not need to do that! You can test your changes through browser extensions.)

This guide is more suited towards Unix-based operating systems, so it'd be recommended to use WSL to follow these instructions on Windows.

### Prerequisites

Install these things before you continue:

- [Git](https://git-scm.com/)
- [Git LFS](https://git-lfs.github.com/)
- [PHP](https://www.php.net/) with `mbstring`, `mysqli` and `zip` extensions
- [MariaDB](https://mariadb.com/) (or MySQL)

For Debian-based distributions, you can install the dependencies with:

```
sudo apt-get install -y git git-lfs mariadb-server php php-mbstring php-mysql php-zip
```

### Clone the repository

Clone this repository into `/srv/dumb` on your system. (If you'd like to clone it into a different directory, make sure to change references to `/srv/dumb` in `dump.sql`.)

```
git clone https://github.com/deltaruneboards/forum.git /srv/dumb
cd /srv/dumb
git lfs install
git lfs pull
```

### Configure the forum

Copy `Settings_private.template.php` to `Settings_private.php`. The settings should work out of the box, aside from potential directory changes you might want to do.

### Set up the database

In the MariaDB CLI (e.g. `sudo mariadb`) run:

```sql
-- Creates the database and the user
CREATE DATABASE IF NOT EXISTS deltaruneboards;
CREATE USER IF NOT EXISTS 'dumb'@'localhost' IDENTIFIED BY 'aaaaaaaaaaaaaaaa';
GRANT ALL PRIVILEGES ON deltaruneboards.* TO 'dumb'@'localhost';
USE deltaruneboards;
-- Imports the database
SOURCE dump.sql;
```

### Running the forum

You can use the PHP built-in webserver to serve the forum. Use port 4000:

```
php -S 127.0.0.1:4000
```

You can then view the forum at http://localhost:4000/. Log in using the account `test2` with password `aaaabbbb`.

## Notes

### Updating `dump.sql`

After we change some settings on the DUMB, we might need to update some of the following tables in `dump.sql`:

- `smf_arcade_modsettings` (except `arcadeRandomIdVar` and `arcadeSecretIdVar`)
- `smf_board_permissions`
- `smf_board_permissions_view`
- `smf_boards`
- `smf_categories`
- `smf_membergroups`
- `smf_permission_profiles`
- `smf_permissions`
- `smf_scheduled_tasks`
- `smf_settings` (except `avatar_url`)
- `smf_smiley_files`
- `smf_smileys`
- `smf_stshop_categories`
- `smf_stshop_modules`

After updating, the forum URL in the dump should be replaced with `http://localhost:4000`, and document root path with `/srv/dumb`.
