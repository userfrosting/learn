---
title: Using Git for Deployment
metadata:
    description: Once you've set up a remote repository in your production environment, deployment can be as simple as a single `git push` command.
taxonomy:
    category: docs
---
<!-- [plugin:content-inject](/modular/_update5.0) -->

[notice]This page needs updating. To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).[/notice]

As we explained earlier, `git` is a good tool for deployment because it keeps track of the changes to your codebase between commits. Once you've set up a remote repository in your production environment, deployment can be as simple as a single `git push` command. Git will automatically determine which files need to be updated on the live server.

We can also use the git `post-receive` hook to run additional build tasks after the code base is updated, like clearing the cache and recompiling assets.

## Introduction

This guide assumes that you already set up your UserFrosting project as a git repository during installation. If not, [go back to the installation guide](/installation/environment/native).

This guide also assumes that you are **regularly committing changes to your repository.** Git can **only** push files to the production server if it is [tracking them](https://www.atlassian.com/git/tutorials/saving-changes#git-add) and you have [committed your changes](https://www.atlassian.com/git/tutorials/saving-changes#git-commit). If you are new to git and _don't understand what this means_, we strongly suggest you check out the free git tutorials from [Github](https://try.github.io) or [Atlassian](https://www.atlassian.com/git/tutorials/learn-git-with-bitbucket-cloud) before you continue.

[notice=warning]Your custom code will not get deployed unless you **add new files** and **commit changes** with git to your repository.[/notice]

## SSH into remote machine

Before you can do anything else, you need to first `ssh` into the remote machine. In general, I like to keep two terminals open at the same time - one connected to my remote machine, and the other for performing local commands.

First, we'll set up a bare repository on the remote server. The reason we use a _bare_ repository is because it separates the location of the repository (the files managed by git that live in the `.git` directory), and the **working tree** (the files you normally work with and from which you commit to the repository).

If we used a non-bare repository, we wouldn't be able to "push to production" because git [does not allow you to push to a checked-out branch](https://stackoverflow.com/questions/20206502/why-use-a-git-bare-repository-for-website-deployment). Since the checked-out branch would be the actual set of files that the webserver is running your application on, it would make it impossible to change these files when we do `git push`.

## Create a bare repository

[notice=note]You may need to use `sudo` to run some of these commands.[/notice]

By default, we'll create a directory `repo` in Ubuntu's `var/` directory that will contain all of our bare repositories on this server.

```bash
sudo mkdir /var/repo
sudo mkdir /var/repo/<repo name>.git
sudo chown <your username>:<your username> /var/repo/<repo name>.git

cd /var/repo/<repo name>.git
git init --bare
```

Replace `<your username>` with the name of the non-root user account you created earlier. You will use this account to push changes from your development environment, so it is important that this account have read and execute permissions on the repo directory.

Replace `<repo name>` with the name of your project. For example, `owlfancy`.

If successful, you should see a message like:

```
Initialized empty Git repository in /var/repo/<repo name>.git
```

## Set up the working directory

The next thing we'll do is set up the **working directory** where our files will live and be served from by the webserver. Traditionally on Ubuntu, `/var/www/` is used for web applications. So, we'll make subdirectories in this directory for each application we deploy on this server.

```bash
sudo mkdir /var/www/<repo name>
sudo chown <your username>:<your username> /var/www/<repo name>
```

Again, we take ownership of the directory so that we'll be able to write to it when we push our application remotely.

## Set up the `post-receive` hook script

The next step is to set up a `post-receive` hook in our _repository_ directory. This hook will tell git to automatically copy the files in the current branch to our working directory every time we deploy.

```bash
nano /var/repo/<repo name>.git/hooks/post-receive
```

This will open up the `nano` text editor. Add the following to this file:

```
#!/bin/sh

# Check the repo out into the working directory for our application
git --work-tree=/var/www/<repo name> --git-dir=/var/repo/<repo name>.git checkout -f

# Clear the UF cache
rm -rf /var/www/<repo name>/app/cache/*
```

Press `Control-X` to exit. When prompted to save, press `Enter` to confirm.

Make sure that your user account has ownership of the `post-receive` file, and the proper permissions to **execute** the script. Specifically, give the user owner and group owner "execute" permissions:

```bash
sudo chmod u+x,g+x /var/repo/<repo name>.git/hooks/post-receive
```

[notice=tip]For more information on file permissions, see the [Unix primer](/going-live/unix-primer-ubuntu#viewing-and-basic-concepts).[/notice]

## Push your project for the first time

Before we can finish configuring our application to run on the live server, we need to push it for the first time. Back on our **local** (development) environment, we'll set up our Droplet as a remote. In your root project directory:

```bash
git remote add live ssh://<your username>@<hostname>/var/repo/<repo name>.git
```

`<your username>` should be the user account on your Droplet that you gave permissions earlier to push and execute the `post-receive` script. `<hostname>` can be the IP address of your Droplet, or any domain/subdomain that resolves to this IP address (if you've already configured DNS for your application).


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

1. git will try to push your files to the bare repository on the production server. Notice that we set up `live` with an `ssh://` url. This means that git will try to connect to your server as the specified user (`alex`) via SSH. If you get an error, double-check your SSH setup.
2. **On the production server**, git will try to run the `post-receive` script. If there is something wrong with your user's permissions, the script might silently fail to execute, or it might fail to check out the repository to the working directory.

## Check that deployment is working properly

So, let's now check to make sure that `post-receive` worked properly. On the **remote** machine, list the files in the working directory:

```bash
ls /var/www/<repo name>
```

You should see your project files. If the directory is empty, then something went wrong with the `post-receive` script - most likely a permissions issue. Fix the issue and try to `push` again.

[notice=warning]If the files were successfully `push`ed to the production server, but the `post-receive` script failed to execute properly, git will just say `Everything up-to-date` and exit **without** rerunning your `post-receive` script. To force git to run the script, you need to make a commit before you rerun `git push`. To make an "empty" commit, use `git commit --allow-empty -m "retry deployment"`. You may want to create a separate deployment branch to avoid polluting your `master` branch with lots of empty commits.[/notice]

Once you have your `git push` working properly, congratulations! Deploying updates to your live application is as simple as running `git push live master` again.

The next step is to [configure UserFrosting for production](/going-live/vps-production-environment/application-setup).
