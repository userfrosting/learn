---
title: Backing up Your Data
metadata:
    description: If you're using version control and use a reliable central repository, your code is generally safe from data loss due to hardware failure, and accidents. But what about your live database? In this chapter, we'll show you how to set up automated, encrypted daily backups with Duplicity.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_updateRequired)

[notice]This page needs updating. To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).[/notice]

It is extremely important to have a system in place to make regular, automated backups of your production database. Even if no one is [maliciously targeting your data](http://fieldguide.gizmodo.com/a-solid-backup-system-is-the-best-protection-against-ra-1795682989), [accidents](https://about.gitlab.com/2017/02/01/gitlab-dot-com-database-incident/) happen [all the time](https://np.reddit.com/r/cscareerquestions/comments/6ez8ag/accidentally_destroyed_production_database_on/).

In this guide, we'll use a free open-source program called Duplicity to set this up. Our goals are that this system should:

1. Perform a full backup every week;
2. Perform an [incremental backup](https://en.wikipedia.org/wiki/Incremental_backup) every night;
3. Store the backups with a free cloud-based storage service (Google Drive);
4. Encrypt the backups, in case your Google account is compromised;
5. Automate this entire process.

[notice=warning]This guide assumes that you are using a MySQL/MariaDB database. The database commands may be different for other database technologies.[/notice]

## Google account

Choose an existing, or create a new Google account where you will store your backups. I like to have a separate account so my business backup data doesn't get mixed in with my personal Google account stuff.

We will refer to the Google account name as `<google_account_name>`.

## Set up database dumps

On the production server, we need to set up the database to dump its contents into a compressed `.sql` file. To do this, we'll create a special directory for database dumps, and use the `mysqldump` command.

### Create a backup directory and give yourself necessary permissions

Ubuntu should automatically come with a `backup` user and group and a `var/backups` directory, so [let's use those](https://askubuntu.com/questions/63685/what-is-the-default-backup-user-for)!

#### Make a subdirectory in `/var/backups` and give ownership to the `backup` group

```bash
sudo mkdir /var/backups/<repo name>
sudo chgrp backup /var/backups/<repo name>
```

#### Give the `backup` group read and write permissions on this directory

```bash
sudo chmod g+rw /var/backups/<repo name>
```

#### Add your own account to the `backup` group

```bash
sudo usermod -aG backup <username>
```

This will make it easier for you to access the contents of the backup directory.

You may need to log out and then log back in for the new group membership to take effect. To check your group membership, use the command `groups`.

### Create a special SQL user account for performing backups

#### Log into MySQL through the command line

```bash
sudo mysql -u root -p
```

You'll be prompted for your `sudo` password first, followed by your root MySQL user password.

#### Create a new database user account

We don't want to make ourselves vulnerable by [giving any more permissions than we absolutely have to](https://en.wikipedia.org/wiki/Principle_of_least_privilege). Thus, we will create a new database user account with read-only privileges. To stay consistent, I'm also calling this _database_ user `backup`. Pick a very strong password for `<db_password>`.

`CREATE USER backup@localhost IDENTIFIED BY '<db_password>';`

#### Grant read-only privileges

`GRANT SELECT,EVENT,TRIGGER,SHOW DATABASES ON *.* TO backup@localhost;`

To quit the database shell, use the command `quit;`.

### Set up the DB backup command

Test out the dump command (replace `<db_password>` with the password you set earlier for the new MySQL user):

```bash
mysqldump --single-transaction --routines --events --triggers --add-drop-table --extended-insert --max-allowed-packet=1000000000 -u backup -h 127.0.0.1 -p<db_password> --all-databases | gzip -9 | sudo tee /var/backups/<repo name>/sql/all_$(date +"%Y_week_%U").sql.gz > /dev/null
```

[notice=warning]Note that there is no space between the `-p` flag and your password.[/notice]

This command will dump all databases to a single file, labeled with the year and current week number. Each time we run this, it will update the current dump file. However when a new week commences, it will end up creating a new file instead. Thus, we maintain a history of weekly snapshots of our databases. You can adjust the [date portion](http://www.computerhope.com/unix/udate.htm) to make these snapshots more or less frequent, depending on the size of your database and the space you're willing to allocate for these snapshots.

We use `sudo tee` here to write the output to a directory for which `mysql` would otherwise not have the proper permissions under our shell.

## Install Duplicity

The current stable version as of June 2017 is 0.7.13.1.

```bash
sudo add-apt-repository ppa:duplicity-team/ppa
sudo apt-get update
sudo apt-get install duplicity
```

## Install PyDrive

PyDrive is a library that handles the OAuth2 negotiation between Duplicity and the Google Drive API. We'll install it with the `pip` package manager for Python, which we need to install first:

```bash
sudo apt-get install python-pip
```

Now we can install pydrive:

```bash
sudo -H pip install pydrive
```

## Set up Google Drive authentication via OAuth2

### Create API credentials

Do this through Google's [Developer Console](https://console.developers.google.com/). For help with this, see:

- [How do I backup to google drive using duplicity?](https://stackoverflow.com/questions/31370102/how-do-i-backup-to-google-drive-using-duplicity)
- [Encrypted Linux Backup with Google Drive and Duplicity](http://6ftdan.com/danielpclark/2016/04/21/encrypted-linux-backup-with-google-drive-and-duplicity)

### Create the PyDrive config file

PyDrive [uses this file to store credentials and configuration settings for the Google API](http://pythonhosted.org/PyDrive/oauth.html).

```bash
mkdir ~/.duplicity
nano ~/.duplicity/credentials
```

Add the following:

```
client_config_backend: settings
client_config:
   client_id: <your client ID>.apps.googleusercontent.com
   client_secret: <your client secret>
save_credentials: True
save_credentials_backend: file
save_credentials_file: /home/<username>/.duplicity/gdrive.cache
get_refresh_token: True
```

Replace `<username>` with your non-root user account name. Replace `<your client ID>` and `<your client secret>` with the values obtained in the previous step.

### Set up the `GOOGLE_DRIVE_SETTINGS` environment variable

```bash
export GOOGLE_DRIVE_SETTINGS=/home/<username>/.duplicity/credentials
```

I would also recommend adding `GOOGLE_DRIVE_SETTINGS` to [sudo environment variables](https://help.ubuntu.com/community/EnvironmentVariables):

```bash
sudo visudo
```

Add the following line at the end:

```
# PyDrive settings
Defaults env_keep += "GOOGLE_DRIVE_SETTINGS"
```

## Create GPG Key

You will need a [GPG](https://www.gnupg.org/) key to encrypt your backup data before it is sent to Google Drive. To generate the key, simply run the command:

```bash
gpg --gen-key
```

Follow the instructions it provides, choosing the defaults for key type, size, and expiration. Make sure you choose a good **passphrase**. If it gets stuck with a message about "not enough entropy", you can try running `sudo apt-get install rng-tools` (log into a separate terminal to do this). The installation itself should generate enough entropy that GPG can generate a truly random key. See [this article](https://stackoverflow.com/a/12716881/2970321).

The GPG "fingerprint" will be displayed after this completes. You will need the **primary public key id** from this fingerprint. This is simply the 8-digit hex code after the `/` on the line that begins with `pub`. See [this explanation](https://security.stackexchange.com/a/110146/74909).

### Add the passphrase that you set for your GPG key to a secret file

```bash
sudo nano /root/.passphrase
sudo chmod 700 /root/.passphrase
```

In this file, simply add:

```
PASSPHRASE="<my passphrase>"
```

### Backup your GPG key

**If you lose your GPG key, your encrypted backups will become useless.** So, you should back up your GPG key to some place besides your VPS.

For example, to backup to your local machine:

```bash
gpg --list-keys
gpg -ao ~/gpg-public.key --export <gpg_public_key_id>

gpg --list-secret-keys
gpg -ao ~/gpg-private.key --export-secret-keys <gpg_private_key_id>
```

Then on your local machine:

```bash
scp <username>@<hostname>:~/gpg-public.key ~/gpg-public.key
scp <username>@<hostname>:~/gpg-private.key ~/gpg-private.key
```

See [this article](https://help.ubuntu.com/community/GnuPrivacyGuardHowto#Backing_up_and_restoring_your_keypair) for more information on backing up your GPG key. Depending on the nature of your data, you may want to consider putting the *private* portion of your GPG key on a piece of paper, and then [storing that piece of paper in a safe](https://security.stackexchange.com/a/51776/74909).

Remove these backups from your home directory on the **remote** machine:

```bash
rm ~/gpg-private.key ~/gpg-public.key
```

## Test unencrypted fake backup

[Reference](https://www.digitalocean.com/community/tutorials/how-to-use-duplicity-with-gpg-to-securely-automate-backups-on-ubuntu)

We'll create some test files, just to check that we can transfer them to Google Drive using Duplicity successfully.

### Create test files

```bash
cd ~
mkdir test
touch test/file{1..100}
```

### Run Duplicity

```bash
duplicity ~/test gdocs://<google_account_name>@gmail.com/backup
```

Follow the verification link it creates, and copy-paste the verification code you receive back into the prompt. Duplicity *should* store the auth token it creates in `/home/<username>/.duplicity/gdrive.cache` so that we don't have to do the verification step again (and so our system can automatically do this every night without our input).

[notice=warning]Make sure the target directory on the remote (Google Drive) does not contain any duplicity backups made with old/other GPG keys. Otherwise, Duplicity will try to synchronize with these backups and fail because the public keys do not match.[/notice]

You should see three files show up in your Google Drive backup directory:

```
duplicity-full.<time>.manifest.gpg
duplicity-full-signatures.<time>.sigtar.gpg
duplicity-full.<time>.vol1.difftar.gpg
```

Delete these files, so that Duplicity won't try to synchronize them when we change our target path for the real data.

## Test encrypted backup of SQL dumps

```bash
duplicity --encrypt-key <gpg_public_key_id> /var/backups/<repo name>/sql gdocs://<google_account_name>@gmail.com/backup
```

## Put the database dump and Duplicity command together into a `cron` script

We'll use a `cron` script to automatically perform the database dumps and run Duplicity.

### Set up daily incremental backup

This will run every night, creating incremental backups of everything in our target path. Duplicity by default tries to back up ALL files in the target path.

[notice=note]Use the `--exclude` parameter so that Duplicity ignores everything except the directories we include via `--include`. You can use multiple `--include` parameters to include multiple directories.[/notice]

Instead of adding to our normal `crontab`, we'll create a dedicated cron script in the `cron.daily` directory:

```bash
sudo nano /etc/cron.daily/duplicity-inc
```

The `-inc` stands for "incremental". Add the following:

```
#!/bin/sh

test -x $(which duplicity) || exit 0
. /root/.passphrase

export PASSPHRASE
export GOOGLE_DRIVE_SETTINGS=/home/<username>/.duplicity/credentials

# This lets the script find your GPG keys when it is running as root
export GNUPGHOME=/home/<username>/.gnupg

# Run MySQL dump. This will create a weekly file, and then update the file every additional time this script is run
mysqldump --single-transaction --routines --events --triggers --add-drop-table --extended-insert --max-allowed-packet=1000000000 -u backup -h 127.0.0.1 -p<password> --all-databases | gzip -9 > /var/backups/<repo name>/sql/all_$(date +"%Y_week_%U").sql.gz

# Performs an incremental backup by default. Since we create a new dump file every week, we have a history
# of weekly snapshots, and the current week is incrementally updated each day.
duplicity --encrypt-key <gpg_public_key_id> /var/backups/<repo name>/sql gdocs://<google_account_name>@gmail.com/backup
```

Again, be sure to replace things in `<>` placeholders with their actual values.

#### Set permissions on the cron script

```bash
sudo chmod 755 /etc/cron.daily/duplicity-inc
```

### Set up a weekly full backup

This will run once a week, creating a full backup and clearing out all but the last three full backups to save space. Again, you can adjust this frequency and number of backups to retain, to your situation.

```bash
sudo nano /etc/cron.weekly/duplicity-full
```

In this file, write:

```
#!/bin/sh

test -x $(which duplicity) || exit 0
. /root/.passphrase

export PASSPHRASE
export GOOGLE_DRIVE_SETTINGS=/home/<username>/.duplicity/credentials
# This lets the script find your GPG keys when it is running as root
export GNUPGHOME=/home/<username>/.gnupg

# Run MySQL dump. This will create a weekly file, and then update the file every additional time this script is run
mysqldump --single-transaction --routines --events --triggers --add-drop-table --extended-insert --max-allowed-packet=1000000000 -u backup -h 127.0.0.1 -p<password> --all-databases | gzip -9 > /var/backups/<repo name>/sql/all_$(date +"%Y_week_%U").sql.gz

# Create a brand new full backup, which contains all the weekly dumps located in /var/backups/sql
duplicity full --encrypt-key <gpg_public_key_id> /var/backups/<repo name>/sql gdocs://<google_account_name>@gmail.com/backup

# Clean out old full backups
duplicity remove-all-but-n-full 3 --force gdocs://<google_account_name>@gmail.com/backup
```

#### Set permissions on the cron script

```bash
chmod 755 /etc/cron.weekly/duplicity-full
```

If your tasks in these `cron.*` directories aren't being run automatically for some reason (often times, due to problems with permissions), you can add these tasks to the root cron file:

```bash
sudo crontab -e
```

Add the lines (replace MM and HH with the minute and hour of the day you want to run the backup; try to pick odd times):

```
# Incremental backup every day at HH:MM
MM HH * * * /etc/cron.daily/duplicity-inc >> /var/log/duplicity.log 2>&1
# Full backup every Saturday at HH:MM
MM HH * * 6 /etc/cron.weekly/duplicity-full >> /var/log/duplicity.log 2>&1
```

Save and exit.

## Test and verify backup

You can try downloading your backup from Google Drive back into `~/test`:

`sudo duplicity gdocs://<google_account_name>@gmail.com/backup ~/test`

Duplicity should fetch and decrypt your latest backup into `~/test`. Unzip it using `gzip`:

```bash
ls ~/test
gzip -d <filename>.sql.gz
```

And check to make sure it looks ok:

```bash
head -n 100 <filename>.sql
```
