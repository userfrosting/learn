---
title: Unix Primer for Ubuntu
metadata:
    description: This chapter covers the most common commands and files that web developers might encounter when working with Ubuntu.
taxonomy:
    category: docs
---

[notice]This page needs updating. To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).[/notice]

## Basic commands

[notice=tip]When you precede a path with `/`, this indicates an **absolute** file path.  For example, `cd /` changes into the root directory, and `cd /var/log` changes into the log directory - no matter what directory you're currently in.[/notice]

### Files

#### List files in a directory

`ls path/`

#### List all files in a directory with permissions and include hidden files

`ls -la path/`

#### Change into a directory

`cd path/`

#### View an entire file in the console

`cat file.txt`

#### View the first 100 lines of a file

`head -n 100 file.txt`

#### View the last 100 lines of a file

`tail -n 100 file.txt`

#### Launch `nano` to create/edit a file

`nano file.txt`

Use Ctrl+X to exit `nano`.

#### Create a directory

`mkdir path/`

#### Remove a file

`rm file`

#### Remove a directory and its contents

`rm -r path/`

#### Create a symbolic link to a file

`sudo ln -s <file-name> <link-name>`

#### Compress and archive one or more files (tar.gz)

`tar -czvf archive.tar.gz /path`

#### Decompress and extract an archive to a specified directory

`tar -xzvf archive.tar.gz -C /path`

For this command, you can use the `--strip-components=n` flag to strip `n` subdirectory paths from the files in the original archive.

### File permissions

#### Viewing and basic concepts

To see the current owner and permissions for all files in a directory, use the `ls -l` command.

```bash
$ ls -l /var/repo/userfrosting.git

total 44
-rwxrwxr-x 1 alex alex  478 Feb 16 02:04 applypatch-msg.sample
-rwxrwxr-x 1 alex alex  896 Feb 16 02:04 commit-msg.sample
-rwxr-xr-- 1 alex alex  138 Feb 22 02:18 post-receive
-rwxrwxr-x 1 alex alex  189 Feb 16 02:04 post-update.sample
-rwxrwxr-x 1 alex alex  424 Feb 16 02:04 pre-applypatch.sample
-rwxrwxr-x 1 alex alex 1642 Feb 16 02:04 pre-commit.sample
-rwxrwxr-x 1 alex alex 1239 Feb 16 02:04 prepare-commit-msg.sample
-rwxrwxr-x 1 alex alex 1348 Feb 16 02:04 pre-push.sample
-rwxrwxr-x 1 alex alex 4898 Feb 16 02:04 pre-rebase.sample
-rwxrwxr-x 1 alex alex 3610 Feb 16 02:04 update.sample
```

The first part of each line contains a `-`, followed by three sets of three characters each.  The first set of three tells you the owning user's permissions, the second set tells you the owning group's permissions, and the last set tells you the permissions for "other" users.  "Other" users means any other Ubuntu user account on your server.

The next pieces of significant information are the name of the **owning user** and **owning group**.  This is important, because Unix permissions are always defined relative to the owning user and owning group.

For example, let's look at this entry:

```bash
-rwxr-xr-- 1 alex alex  138 Feb 22 02:18 post-receive
```

We can see that the owning user is `alex`, and the owning group is also `alex` (Whenever you create a user in Linux, it automatically creates a group of the same name).

Then we have the following permissions:

- **User**: `rwx`.  The owning user (`alex`) has full read, write, and execute permissions for this file.
- **Group**: `r-x`.  The owning group (`alex`) has read and execute permissions for this file.
- **Other**: `r--`.  Other users only have read permissions for this file.

[notice]"User" in this context refers to the _operating system's_ users.  User accounts in your UserFrosting application are **not** users on the operating system.  Visitors to your website can only interact with the files on your machine through the webserver and your application.[/notice]

#### Changing the owning user/group for a file

```bash
sudo chown <user>:<group> file.txt
```

#### Changing permissions

```bash
sudo chmod u+r,g+r,o-w file.txt
```

The arguments `u`, `g`, and `o` refer to the owning **u**ser, owning **g**roup, and **o**ther users, respectively.  The `+` symbol means that we are **adding** permissions (use `-` instead to remove permissions).  The symbols afterwards are the permissions we are adding/removing.  They can be any combination of `r` (read), `w` (write), and `x` (execute).

#### Set default permissions

When new files are created in Unix, they get their permissions primarily from the **file mode creation mask**, also known as the **umask**.

If we want to change how permissions are set on newly created files in a directory (for example, when pushed there by `git`), we can use `setfacl`:

```bash
sudo setfacl -d -m g::rwx /my/path
```

The `-d` indicates **default** permissions, and `-m g::rwx` says to grant read, write, and execute permissions for the owning group (`g`) on any new files created in the directory.

Just as with `chmod`, you can use `u` for the owning user, or `o` for "all other users".

### Package management

#### Install a package

`apt-get install <package-name>`

#### Update a package

`apt-get upgrade <package-name>`

#### Remove a package

`apt-get remove <package-name>`

### Processes

#### Search for a process

`ps aux | grep "<description>"`

This will display a list of matching processes, along with their process id (pid).

#### Kill a process by pid

First try `kill <pid>`.  If that doesn't work, try `kill -9 <pid>`.

## Default locations in Ubuntu

| Path                          | Description                                         | Notes                           |
| ----------------------------- | --------------------------------------------------- | ------------------------------- |
| `/var/www/`                   | Home directory for web applications.                |                                 |
| `/var/log/`                   | Default directory for most logs.                    |                                 |
| `/etc/nginx/sites-available/` | Physical location of configuration files for nginx. |                                 |
| `/etc/nginx/sites-enabled/`   | Symbolic links to 'enabled' nginx config files.     |                                 |
| `/etc/letsencrypt/archive/`   | Physical location of LE certificate files.          | Default only accessible to root |
| `/etc/letsencrypt/live/`      | Symbolic links to current LE certificate files.     | Default only accessible to root |
| `/etc/letsencrypt/renewal/`   | Renewal scripts for LE certificates.                | Default only accessible to root |

## User accounts

- `root`: The root user.  Has the highest level of privileges.
- `www-data`: The default account under which `nginx` and `apache` run.
- `backup`: The "backup" user account.

## Automation With Cron

TODO
