---
title: Available commands
taxonomy:
    category: docs
---

Here a brief list of commands available in the `bakery` CLI tool. You can find help with each command using:

```bash
$ php bakery [command] help
``` 

General help can also be displayed by running:

```bash
$ php bakery help
``` 

## debug
This command will run a series of tests to make sure everything is ready to run UserFrosting on your system. If you have trouble accessing your UserFrosting installation, you should run this command first to make sure basics requirements are met. 

The information displayed by this command can also be useful to other people when [asking for help](/installation/getting-help) and submitting new issues on Github. 

```bash
$ php bakery debug
``` 

## setup

!TODO

```bash
$ php bakery setup
``` 

## build-assets

!TODO

```bash
$ php bakery build-assets
``` 

## migrate

!TODO

```bash
$ php bakery migrate
``` 

### migrate:rollback

!TODO

```bash
$ php bakery migrate:rollback
``` 

### migrate:reset

!TODO

```bash
$ php bakery migrate:reset
``` 

### migrate:refresh

!TODO

```bash
$ php bakery migrate:refresh
``` 

## test

!TODO

```bash
$ php bakery test
``` 

## bake
Bake is the general installation command. It combines `setup`, `debug`, `migrate` and `build-assets` into a single command : 

```bash
$ php bakery bake
``` 