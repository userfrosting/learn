---
title: Using Git for Deployment
metadata:
    description: Once you've set up a remote repository in your production environment, deployment can be as simple as a single `git push` command.
taxonomy:
    category: docs
---

As we explained earlier, `git` is a good tool for deployment because it keeps track of the changes to your codebase between commits.  Once you've set up a remote repository in your production environment, deployment can be as simple as a single `git push` command.  Git will automatically determine which files need to be updated on the live server.

We can also use the git `post-receive` hook to run additional build tasks after the code base is updated, like clearing the cache and recompiling assets.

## Introduction

This guide assumes that you already set up your UserFrosting project as a git repository during installation.  If not, [go back to the installation guide](/installation/environment/native).

Before you can do anything else, you need to first `ssh` into the remote machine.  In general, I like to keep two terminals open at the same time - one connected to my remote machine, and the other for performing local commands.

First, we'll set up a bare repository on the remote server.  The reason we use a _bare_ repository is because it separates the location of the repository (the files managed by git that live in the `.git` directory), and the **working tree** (the files you normally work with and from which you commit to the repository).

If we used a non-bare repository, we wouldn't be able to "push to production" because git [does not allow you to push to a checked-out branch](https://stackoverflow.com/questions/20206502/why-use-a-git-bare-repository-for-website-deployment).  Since the checked-out branch would be the actual set of files that the webserver is running your application on, it would make it impossible to change these files when we do `git push`.

## Creating a bare repository

>>>>> You may need to use `sudo` to run some of these commands.

By default, we'll create a directory `repo` in Ubuntu's `var/` directory that will contain all of our bare repositories on this server.

```bash
sudo mkdir /var/repo/<repo name>.git
sudo chown <your username>:<your username> /var/repo/<repo name>.git

cd /var/repo/<repo name>.git
git init --bare
```

Replace `<your username>` with the name of the non-root user account you created earlier.  You will use this account to push changes from your development environment, so it is important that this account have read and execute permissions on the repo directory.

Replace `<repo name>` with the name of your project.  For example, `owlfancy`.

## Set up the working directory

The next thing we'll do is set up the **working directory** where our files will live and be served from by the webserver.  Traditionally on Ubuntu, `/var/www/` is used for web applications.  So, we'll make subdirectories in this directory for each application we deploy on this server.

```bash
sudo mkdir /var/www/<repo name>
sudo chown <your username>:<your username> /var/www/<repo name>
```

Again, we take ownership of the directory so that we'll be able to write to it when we push our application remotely.

## Set up the `post-receive` hook script

The next step is to set up a `post-receive` hook in our _repository_ directory.  This hook will tell git to automatically copy the files in the current branch to our working directory every time we deploy.

```bash
nano /var/repo/<repo name>.git/hooks/post-receive
```

This will open up the `nano` text editor.  Add the following to this file:

```
#!/bin/sh

# Check the repo out into the working directory for our application
git --work-tree=/var/www/<repo name> --git-dir=/var/repo/<repo name>.git checkout -f

# Clear the UF cache
rm -rf /var/www/<repo name>/app/cache/*
```

Press `Control-X` to exit.  When prompted to save, press `Enter` to confirm.

Make sure that your user account has ownership of the `post-receive` file, and the proper permissions to execute the script.

## Viewing file permissions

To see the current owner and permissions for all files in a directory, use the `ls -l` command.

```bash
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

The next pieces of significant information are the name of the owning user and owning group.  This is important, because Linux permissions are always defined relative to the owning user and owning group.

For example, let's look at this entry:

```bash
-rwxr-xr-- 1 alex alex  138 Feb 22 02:18 post-receive
```

We can see that the owning user is `alex`, and the owning group is also `alex` (Whenever you create a user in Linux, it automatically creates a group of the same name).

Then we have the following permissions:

- **User**: `rwx`.  The owning user (`alex`) has full read, write, and execute permissions for this file.
- **Group**: `r-x`.  The owning group (`alex`) has read and execute permissions for this file.
- **Other**: `r--`.  Other users only have read permissions for this file.

>>> "User" in this context refers to the _operating system's_ users.  User accounts in your UserFrosting application are **not** users on the operating system.  Visitors to your website can only interact with the files on your machine through the webserver and your application.

If your permissions for the `post-receive` script don't seem to match these permissions, you can use `chmod` to change them.  For example, to give the user owner and group owner "execute" permissions , you can use the following:

```bash
sudo chmod u+x,g+x /var/repo/<repo name>.git/hooks/post-receive`
```

The arguments `u`, `g`, and `o` refer to the owning **u**ser, owning **g**roup, and **o**ther users, respectively.  The `+` symbol means that we are **adding** permissions (use `-` instead to remove permissions).  The symbols afterwards are the permissions we are adding/removing.  They can be any combination of `r` (read), `w` (write), and `x` (execute).

## Push your project for the first time

Before we can finish configuring our application to run on the live server, we need to push it for the first time.  Back on our **local** (development) environment, we'll set up our Droplet as a remote.  In your root project directory:

```bash
git remote add live ssh://<your username>@<hostname>/var/repo/<repo name>.git
```

`<your username>` should be the user account on your Droplet that you gave permissions earlier to push and execute the `post-receive` script.  `<hostname>` can be the IP address of your Droplet, or any domain/subdomain that resolves to this IP address (if you've already configured DNS for your application).


Now, if you type `git remote -v`, you should see a list of your current remotes:

```
$ git remote -v
live	ssh://alex@owlfancy.com/var/repo/owlfancy.git (fetch)
live	ssh://alex@owlfancy.com/var/repo/owlfancy.git (push)
origin	https://alex@bitbucket.org/owlfancy/owlfancy.git (fetch)
origin	https://alex@bitbucket.org/owlfancy/owlfancy.git (push)
upstream	https://github.com/userfrosting/UserFrosting.git (fetch)
upstream	no-pushing (push)
```

- `upstream` is the master UserFrosting repository, from which we can pull updates to UF;
- `origin` points to our collaborative development repository on Bitbucket, which gives us free private repos;
- `live` points to our live production server.

**To push to production for the first time**, we'll use the command:

```
git push live master
```

When we do this, two things will happen:

1. git will try to push your files to the bare repository on the production server.  Notice that we set up `live` with an `ssh://` url.  This means that git will try to connect to your server as the specified user (`alex`) via SSH.  If you get an error, double-check your SSH setup.
2. **On the production server**, git will try to run the `post-receive` script.  If there is something wrong with your user's permissions, the script might silently fail to execute, or it might fail to check out the repository to the working directory.

## Check that deployment is working properly

So, let's now check to make sure that `post-receive` worked properly.  On the **remote** machine, list the files in the working directory:

```bash
ls /var/www/<repo name>
```

You should see your project files.  If the directory is empty, then something went wrong with the `post-receive` script - most likely a permissions issue.  Fix the issue and try to `push` again.

>>>> If the files were successfully `push`ed to the production server, but the `post-receive` script failed to execute properly, git will just say `Everything up-to-date` and exit **without** rerunning your `post-receive` script.  To force git to run the script, you need to make a commit before you rerun `git push`.  To make an "empty" commit, use `git commit --allow-empty -m "retry deployment"`.  You may want to create a separate deployment branch to avoid polluting your `master` branch with lots of empty commits.

Once you have your `git push` working properly, congratulations!  Deploying updates to your live application is as simple as running `git push live master` again.

The next step is to [configure UserFrosting for production](/going-live/vps-production-environment/application-setup).
