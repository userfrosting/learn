---
title: Mail Providers
metadata:
    description: Email is essential for many of UserFrosting's account features, including account verification and password reset requests.
taxonomy:
    category: docs
---

By default, UserFrosting is configured to use an external SMTP mail server of your choice.

Why? Because dual-purposing your web server as an ad hoc mail server (i.e., what you get when you use PHP's `mail()` function) tends to lead to [all sorts of problems](http://blog.teamtreehouse.com/sending-email-with-phpmailer-and-smtp). The biggest problem is that your messages stand a good chance of being marked as spam, or blocked outright, by other mail servers. To understand this, consider how email works:

![Mail transport chain](/images/mail-transport.png)

To send a message to someone's email address, the sending mail server must ultimately ask another mail server to deliver the message to the email address on your behalf - there is no way to directly place a message in someone's inbox! For example, when you send a message to `grandma@compuserve.net`, you are actually asking some other mail server (e.g., `mail.compuserve.net`) to deliver your message to the address.

Of course, `mail.compuserve.net`, like all email servers, has a problem with spam and phishing attacks. If they just accept from any random mail server, the accounts that they deliver mail to will quickly be flooded with all sorts of junk. Grandma will get hacked.

For this reason, many mail servers are configured to outright reject mail from "untrusted" servers. What makes a server untrusted? Well, pretty much anything:

- IP/domain has a sketchy history;
- IP/domain is on a public blacklist like [Spamhaus](https://www.spamhaus.org/lookup/);
- IP/domain is on an internal blacklist;
- IP/domain is _not_ on an internal whitelist;
- Sending server is not configured properly (DKIM, SPF, DNSSEC, etc)

Getting everything just right (especially getting onto certain email providers' whitelists) can take a long time and be tricky. You could spend a long time getting this just right on your web host server, only to have to start all over again when you decide to move your hosting to another service or IP address!

Best practice, therefore, is to use a completely separate server to send mail for your application. Your application will _authenticate_ with this server when it wants to send out a message. Therefore, you usually need to sign up in some way to obtain credentials.

## Choosing a mail service provider

Your options basically come down to:

1. Use a free third-party service (usually limited in what you can do);
2. Use a paid third-party service (can get expensive);
3. Run your own (separate) mail server (takes a lot of time).

In addition, you might also want to be able to _receive_ email - for example, if you have a website hosted at `http://www.owlfancy.com`, but you _also_ want people to be able to email you at `david@owlfancy.com`. All of this should this should be taken into account when selecting a mail service provider.

### Free services

#### Elastic Email

Our top recommendation is [Elastic Email](https://elasticemail.com/account#/create-account?r=a4a354f0-eab2-4fe6-a337-199facbf9288), which allows you to send up to 5000 emails per day for free. It also has a very nice dashboard that includes reporting and some basic promotional emailing tools.

| Setting             | Value                           |
| ------------------- | ------------------------------- |
| SMTP server address | `smtp.elasticemail.com`         |
| SMTP user name      | [Your registered email address] |
| SMTP password       | [Elastic Email API Key]         |
| SMTP port           | `2525`                          |

Use our [referral link](https://elasticemail.com/account#/create-account?r=a4a354f0-eab2-4fe6-a337-199facbf9288) when you sign up to help support UserFrosting!

#### Gmail

If you have a Gmail account, you can use Gmail's SMTP servers to send mail from your application. The main limitation, though, is that you can only send _as_ your Gmail account user (and not, for example, `webmaster@owlfancy.com`), and you can't send more than 99 messages per day.

You should also be aware that Google may deem your application as "less secure", and so you will need to [allow less secure applications](https://support.google.com/accounts/answer/6010255) to connect with your Gmail credentials. Please note that the label "less secure" does not mean that UserFrosting has security flaws; rather this label is triggered [because you are allowing third parties to authenticate with your Gmail password rather than a revokeable API key](https://security.stackexchange.com/a/72371/74909).

| Setting             | Value                 |
| ------------------- | --------------------- |
| SMTP server address | `smtp.gmail.com`      |
| SMTP user name      | [Your Gmail address]  |
| SMTP password       | [Your Gmail password] |
| SMTP port           | `465`                 |

For more information, see [this guide](https://www.digitalocean.com/community/tutorials/how-to-use-google-s-smtp-server).

### Zoho Mail

[Zoho mail](https://www.zoho.com/mail/) provides a simple [paid](https://www.zoho.com/mail/zohomail-pricing.html) mail hosting solution for business, but they also offers a free plan. The free plan gives you up to five users, 5GB/User and 25MB attachment limit for single domain.

### Paid services

To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

### Running your own mail server

If you need to host your own email accounts for users to receive mail (e.g., `david@owlfancy.com`), third-party services can become very expensive very quickly. Google's G Suite service, for example, would charge you $5 per user account **per month**! In these situations, running your own mail server would be the wiser option.

If you choose to run your own mail server, we highly recommend [Mail-in-a-box](https://mailinabox.email/). MIAB is a prebuilt distribution, which contains all the software and configuration scripts you need to set up a dedicated mail server. You will need a VPS **separate from your web server and with at least 1GB of memory** to run this on, which at DigitalOcean will cost you about [$10 per month](https://www.digitalocean.com/pricing/#droplet).

Mail-in-a-box will not only set up an SMTP server, but IMAP/POP as well so that you can receive mail for user accounts on a domain associated with your server. It even runs its own web server, to provide a web-based client for these email accounts.

If you are already running UserFrosting in a Docker container you might also use [Mailcow-dockerized](https://mailcow.email), as it doesn't require a separate server.
