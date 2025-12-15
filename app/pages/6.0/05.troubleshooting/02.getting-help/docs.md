---
title: Getting Help
description: Don't be afraid to ask for help! Just, please be sure to read and understand our rules first.
obsolete: true
---

## General tips for support

On Github, UserFrosting Chat, and Stack Overflow, please keep in mind the following:

1. Remember that courtesy and proper grammar go a long way. Please take the time to craft a **precise, polite issue**. We will do our best to help, but remember that this is an open-source project - none of us are getting paid a salary to develop this project, or to act as your personal support hotline ;-). We understand the language barrier can be an issue for some people, so don't be afraid to ask questions even if your English is not perfect!

2. Report any errors in detail. Vague issues like "it doesn't work when I do this" are not helpful. Show that you have put some effort into identifying the cause of the error. See the [previous section](/troubleshooting/debugging) for information on how to get more details about errors and other problems. Including a stack trace with your report is the best way we can help you.

3. You should always test your code in a [local development environment](/background/develop-locally-serve-globally), to separate **code-related** issues from **server** issues. In general, we recommend that you install a local development server on your computer, rather than [testing your code directly on the production server](https://pbs.twimg.com/media/BxfENwpIYAAcHqQ.png). This means you can test your code directly on your own computer, making development faster and without the risk of exposing sensitive information to the public.

## Chat

UserFrosting has its very own [chat room](https://chat.userfrosting.com), built with [Rocket.chat](https://rocket.chat/) and hosted thanks to the [generous donations of viewers like you](https://opencollective.com/userfrosting)! Please feel free to stop by any time - we'd love to chat with you and help you out! You may sign in with your GitHub account. But first, please note the following:

### Channels

After joining chat, please be sure to choose the appropriate channel:

- **#support**: Use this channel to get help.
- **#general**: Use this channel if you're bored and you just want to chat, or if you want to discuss something about UF that doesn't belong in *#support*.
- **#github-activity**: Automated notifications from Github. This channel is read-only.
- **#announcements**: General announcement channel. This channel is read-only.

> [!TIP]
> Generally speaking, you're more likely to get a quick response if you post in **#support** rather than sending a direct message to a moderator or admin user directly.

### Chat Rules

#### This is a public chat room.

Please be civil and respectful.

#### Don't ask to ask.

Just ask.

#### Your question may have been answered before.

Check [Stack Overflow](http://stackoverflow.com/tags/userfrosting) and the [issue tracker](https://github.com/userfrosting/UserFrosting/issues?utf8=%E2%9C%93&amp;q=is%3Aissue) first. You can also try searching the chat history.

#### Use Markdown to format blocks of code.

Markdown is the _de facto_ standard for basic text formatting on the web. If you are unfamiliar with Markdown, please [take a few minutes to learn](https://guides.github.com/features/mastering-markdown/#what). It will help you all over the web! In particular, please make sure you know where the **backtick** key (`) is located on your keyboard:

![Location of backtick key](/images/backtick.png)

##### Inline code

Use **single backticks** (`) to format inline code:

<pre>
So you're telling me that `$user->owls()->count()` should work?
</pre>

##### Short blocks of code

Use **triple backticks** (```) to format code blocks:

<pre>
So you're telling me that...

```
$user->owls()->count();
```

...should work?
</pre>

Note that for code blocks, each set of backticks must be on its **own line**. Use Shift+Enter to insert new lines into your message.

##### Longer blocks of code

**For larger blocks of code**, please paste into a [Gist](https://gist.github.com) and then link to your Gist in chat.

##### Images

The easiest way to share images with us, such as screenshots, is to simply **drag them into the chat window**. Please do not upload anything that contains sensitive or private content (ie. passwords).

#### Set an avatar for yourself.

Click the arrow next to your username in the upper left corner, and go to "My Account" => "Avatar".

## GitHub

Our GitHub [issue tracker](https://github.com/userfrosting/UserFrosting/issues?utf8=%E2%9C%93&amp;q=is%3Aissue) is reserved for feature requests and bug reports only. **For troubleshooting and general questions, you should ask on chat and/or Stack Overflow!**

## Stack Exchange

UserFrosting has its very own [Stack Overflow tag](http://stackoverflow.com/questions/tagged/userfrosting).

Also, remember that UserFrosting builds on top of a number of very popular and well-supported packages. Although you can search for questions specifically tagged with `userfrosting`, you should also ask yourself if your question is really about UserFrosting in particular, or about one of the many technologies it builds upon. For example, many questions we get in chat are actually about:

- Apache ([`htaccess`](http://stackoverflow.com/questions/tagged/.htaccess))
- HTTP request/response cycle ([`slim`](http://stackoverflow.com/questions/tagged/slim); [`rest`](http://stackoverflow.com/questions/tagged/rest))
- Database calls ([`eloquent`](http://stackoverflow.com/questions/tagged/eloquent), [`pdo`](http://stackoverflow.com/questions/tagged/pdo))
- Database design (See [Database Administrators Stack Exchange](http://dba.stackexchange.com/))
- Templating and content ([`twig`](http://stackoverflow.com/questions/tagged/twig))
- Client-side code ([`jquery`](http://stackoverflow.com/questions/tagged/jquery), [`bootstrap`](http://stackoverflow.com/questions/tagged/twitter-bootstrap))
- Tools ([`composer-php`](http://stackoverflow.com/questions/tagged/composer-php); [`git`](http://stackoverflow.com/questions/tagged/git))

If you have a **specific, well-researched question**, you may consider posting it to one of:

- [Stack Overflow](http://stackoverflow.com)
- [Database Administrators](http://dba.stackexchange.com/)
- [Software Engineering](http://softwareengineering.stackexchange.com/)
- [Server Fault](http://serverfault.com/) (for network and system administration questions)

Tag your question as `userfrosting`, as well as any other relevant tags (`twig`, `slim`, `eloquent`, `rest`, `jquery`, etc). **Before posting to any Stack community, please make sure your question conforms to their [question guidelines](http://stackoverflow.com/help/on-topic)!** If your question is more open-ended or opinion-based, you should probably just ask directly in chat.

> [!NOTE]
> Once you post a question to Stack Overflow, you should post a link to your question in [chat](https://chat.userfrosting.com) for the fastest response.

You may also find the following communities useful in certain cases:

- [Information Security](http://security.stackexchange.com/)
- [User Experience](http://ux.stackexchange.com/)
- [Webmasters](http://webmasters.stackexchange.com/)
- [Code Review](http://codereview.stackexchange.com/)
- [Unix & Linux](http://unix.stackexchange.com/)
- [Ask Ubuntu](http://askubuntu.com/)
- [Software Recommendations](http://softwarerecs.stackexchange.com/)
- [Software Quality Assurance and Testing](http://sqa.stackexchange.com/)

## Forums

Our forums can be found on [GitHub Discussions](https://github.com/orgs/userfrosting/discussions) for more in-dept and personalized questions.
