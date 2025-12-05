The [command line interface](/installation/requirements/essential-tools-for-php#the-command-line-cli) will be required to perform most tasks in this guide. It's usage depends on your OS : 

#### MacOS 
If you're using MacOS, the **Terminal** is already installed on your computer. You'll find the app in `/System/Applications/Utilities/Terminal`.

#### Linux
Every Linux distro uses the command line. On Ubuntu for example, you can find a launcher for the terminal by clicking on the Activities item at the top left of the screen, then typing the first few letters of "terminal", "command", "prompt" or "shell".

#### Windows
The easiest way to setup a local dev environnement on Windows is through *Windows Subsystem for Linux* (WSL2). This is basically running Linux inside Windows. Best of both worlds! This also means most installation instructions for Windows you'll find on the internet won't work, as we're not technically *on* Windows, **we're on Ubuntu**. We'll instead use the Ubuntu installation instructions! 

See this guide for more detail on this process : [Set up a WSL development environment](https://learn.microsoft.com/en-us/windows/wsl/setup/environment). The gist of it is : 

1. Open *Windows Terminal*, which can be found in the [Microsoft Store](https://apps.microsoft.com/detail/9N0DX20HK701?hl=en-us&gl=US).
2. Open the terminal and install WSL2 distro : `wsl --install`.
3. During installation, enter a unix user with a password. Remember this password, you'll need it later!
4. Restart Windows Terminal and open a new ***Ubuntu*** terminal. Each subsequent CLI usage on Windows will be from this Ubuntu terminal.

When using Windows and WSL2, keep in mind your project files will be stored inside the Linux file system. For example, your project files will be in the Linux file system root directory (`\\wsl$\<DistroName>\home\<UserName>\Project`), not the Windows file system root directory (`C:\Users\<UserName>\Project or /mnt/c/Users/<UserName>/Project$`). See [Microsoft guide on file storage](https://learn.microsoft.com/en-us/windows/wsl/setup/environment#file-storage) for more information. 

> [!TIP]
> Also see the [Get started using Visual Studio Code with Windows Subsystem for Linux](https://learn.microsoft.com/en-us/windows/wsl/tutorials/wsl-vscode) guide if you're using VSCode.
