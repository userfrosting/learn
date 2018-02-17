---
title: Contributing
metadata:
    description: Help out by providing support in our chat and forums, translating content, fixing bugs, and implementing new features.
taxonomy:
    category: docs
---

## Providing support

Our chat room can get very busy!  We have users from all over the world, looking for help 24/7.  Although Alex tends to spend *nearly* every waking minute on his computer, he (and the majority of the other main developers, who mostly live in the UTC-4 timezone) do need to sleep, eat, and work from time to time.

If you feel like you've gotten a handle on UserFrosting, it would be extremely helpful if you can simply keep a browser tab open to our [chat room](https://chat.userfrosting.com) (or install the [Rocket.chat app](https://rocket.chat/download)), and help out other users when you have a chance.  You may also wish to sign up for our [forums](https://forums.userfrosting.com) to help other developers there, as well.

## Contributing code and content

Yes, we welcome pull requests!  Based on your level of skill and background, you might consider contributing to any of the following:

- Language translations
- Implementing feature requests
- Fixing bugs

>>>> Don't start working on a new feature until you've discussed it with a member of the dev team in chat first.  It's always a tragedy when someone spends a lot of time working on a feature, only to have their pull request rejected because their code doesn't fit the technical requirements and/or general vision for UF's future.

Requirements are as follows:

### Style and Coding Standards

Please make sure any PHP code conforms to the [PSR](http://www.php-fig.org/psr/).  CSS and HTML should adhere to the [Code Guide](http://codeguide.co/).

### Git Flow

When making a pull request for a bug fix or translation pack, set your "base branch" to the `hotfix-development` branch. Accepted pull requests will be merged into `master` in batches with a new hotfix number as needed. This will allow us to keep track of which version number every change belongs to, which is useful when tracking down other users' issues.

### Change Log

When submitting code, make sure to add feature/major changes to `README.md` and **all** changes to `CHANGELOG.md`.

>>>>> Since UF4 is fully modular and extendable, additional features might be best implemented as a new [Sprinkle](/sprinkles) rather than being added to the core UserFrosting project.  Join us in chat to discuss an appropriate development plan for your feature.
