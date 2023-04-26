---
title: Don't Reinvent the Wheel
metadata:
    description: Using third-party components reduces the amount of software maintenance you have to do and documentation you have to write, and lets you draw on the wider community of other developers who use those packages for troubleshooting and support.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

I think that for a lot of developers - novices and professionals alike - building on top of others' work can seem like a betrayal of our trade.  We're not "real" developers unless we built everything with our bare hands from scratch, and know firsthand the nitty-gritty details of how our code works.  With third-party components, we have to take time to actually *learn* how to use them, and follow *their* rules.  I get it.  It all feels so antithetical to the DIY spirit that got so many of us into coding in the first place.  Trust me, as someone who built a cold frame out of some doors and framing I found in the dumpster, I know:

![DIY cold frame](/images/cold-frame.jpg?resize=500)

However unlike me with with my cold frame, software developers aren't limited by the contents of their local dumpster.  With the advent of Composer, the PHP community abounds with free, high-quality third-party packages for pretty much every task imaginable.  The trick is to know *which* packages to use, and to avoid getting overwhelmed.

Using third-party components solves a couple of problems.  First and most obviously, we save time it would have taken write the code ourselves.  However for a lot of people, this benefit alone is canceled out by the extra effort involved in learning how to use a particular software package.  Fair enough.  But consider the additional benefits:

### Software maintenance.

It's [well-established](http://www.eng.auburn.edu/~kchang/comp6710/readings/Forgotten_Fundamentals_IEEE_Software_May_2001.pdf) that on average, 60% of time and money spent on a software project goes into maintenance.   Chances are, you won't be the only one using a given package, and this means more opportunities for the community to spot and fix bugs.  Even if you don't care a whit about contributing to open source projects, other people do and you stand to gain tremendously from their efforts.  You're effectively offloading a huge amount of work in debugging and improvement to the communities surrounding those packages.

### Documentation.

In a few months or years, you (and perhaps other people) will have to read the code that you wrote today.  And as we all know, writing code is easy, but reading code is [very, very hard](https://blog.codinghorror.com/when-understanding-means-rewriting/).  Heck, sometimes I struggle to understand code that I wrote just a few months prior!  High-quality packages are already documented for us.  We can build an application using package X, put it down for a few months, and get back to work without having to dig through our code to figure out "how does feature A work?"  A decent software package is already thoroughly documented!

### Community.

Components that are sufficiently popular will likely have an active community in chat rooms, discussion forums, IRC, and Q&A sites.  As much fun as it is to solve everything ourselves, sometimes you really just need to ask for help.  Hopefully as you learn more about a particular package, you will also start to help others.  And as we all know, the best way to master a subject is to teach it to someone else.

Ok, so maybe now you're thinking "but what if I end up using a package that's missing a feature that I realize that I need later?"  That's where the beauty of the open-source community and the social coding movement come in.  You can always make your own copy of a package and modify it to suit your needs (this is Github's "fork" feature).

Of course at this point, the package is no longer a black box.  You'll have to read through someone else's code in order to be able to modify it.  But keep in mind that reading your *own* code from a few months prior can be just as difficult as reading someone else's - perhaps even moreso if their code is carefully documented and yours isn't.  And of course, if you're the type that likes to give back, you can offer to merge your improvements into the main project repository (this is Github's "pull request" feature).

Hopefully I've convinced you by now that there's no real reason not to stand on the shoulders of others whenever possible.

It is, of course, important to pick the *right* packages.  You want to choose packages that are well-maintained by an active community.  This doesn't necessarily mean a large community - often a small but highly active community will be more productive than a large community that is swamped by feature requests, poor management, and more takers than givers.

In building UserFrosting, we have tried to collect what we believe are the best packages that the PHP community has to offer that are needed to build a basic web application.  For functionality beyond the groundwork we've laid, your best bet is to carefully research your options before committing to a specific package.
