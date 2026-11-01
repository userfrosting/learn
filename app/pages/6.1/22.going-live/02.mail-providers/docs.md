---
title: Mail Providers
description: Email is essential for many of UserFrosting's account features, including account verification and password reset requests.
outdated: true
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

Gmail can be used for sending mail from your application with some limitations: you can only send as your Gmail account (not custom addresses like `webmaster@owlfancy.com`), and you're limited to approximately 500 messages per day.

> [!IMPORTANT]
> Google deprecated "less secure app" access. You must now use **App Passwords** to authenticate third-party applications.

**Setup steps:**

1. Enable [2-Factor Authentication](https://myaccount.google.com/security) on your Google account
2. Generate an [App Password](https://myaccount.google.com/apppasswords) specifically for your UserFrosting application
3. Use the generated 16-character App Password (not your regular Gmail password) in your configuration

| Setting             | Value                           |
| ------------------- | ------------------------------- |
| SMTP server address | `smtp.gmail.com`                |
| SMTP user name      | [Your Gmail address]            |
| SMTP password       | [16-character App Password]     |
| SMTP port           | `587` (TLS) or `465` (SSL)      |

For detailed setup instructions, see [Google's App Password guide](https://support.google.com/accounts/answer/185833).

### Zoho Mail

[Zoho mail](https://www.zoho.com/mail/) provides a simple [paid](https://www.zoho.com/mail/zohomail-pricing.html) mail hosting solution for business, but they also offers a free plan. The free plan gives you up to five users, 5GB/User and 25MB attachment limit for single domain.

### Paid services

For production applications with higher email volumes, consider these professional SMTP providers:

- **[SendGrid](https://sendgrid.com/)** - Up to 100 emails/day free, then paid plans from $15/month
- **[Mailgun](https://www.mailgun.com/)** - First 5,000 emails free for 3 months, then pay-as-you-go
- **[Amazon SES](https://aws.amazon.com/ses/)** - $0.10 per 1,000 emails (requires AWS account)
- **[Postmark](https://postmarkapp.com/)** - Starting at $15/month for 10,000 emails
- **[Brevo (formerly Sendinblue)](https://www.brevo.com/)** - 300 emails/day free, then paid plans

These services typically offer better deliverability, detailed analytics, and webhook support for tracking bounces and opens.

### Running your own mail server

If you need to host your own email accounts for users to receive mail (e.g., `david@owlfancy.com`), third-party services can become very expensive very quickly. Google's G Suite service, for example, would charge you $5 per user account **per month**! In these situations, running your own mail server would be the wiser option.

If you choose to run your own mail server, we highly recommend [Mail-in-a-box](https://mailinabox.email/). MIAB is a prebuilt distribution, which contains all the software and configuration scripts you need to set up a dedicated mail server. You will need a VPS **separate from your web server and with at least 1GB of memory** to run this on, which at DigitalOcean will cost you about [$10 per month](https://www.digitalocean.com/pricing/#droplet).

Mail-in-a-box will not only set up an SMTP server, but IMAP/POP as well so that you can receive mail for user accounts on a domain associated with your server. It even runs its own web server, to provide a web-based client for these email accounts.

If you are already running UserFrosting in a Docker container you might also use [Mailcow-dockerized](https://mailcow.email), as it doesn't require a separate server.
