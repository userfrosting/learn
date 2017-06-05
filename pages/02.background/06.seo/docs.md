---
title: Search Engine Optimization
metadata:
    description: Search Engine Optimization (SEO) is an integral part of the design and development process.  We discuss the major important factors in getting a page to rank well, and how they fit in with UserFrosting's features and overall architecture.
taxonomy:
    category: docs
---

Search Engine Optimization (SEO) is an integral part of the design and development process.  Getting the public side of your website to rank well in search results should be something you consider from the very beginning, and not an afterthought once you're getting ready to deploy.

There is no magic formula, and major search engines like Google keep the specifics of their ranking algorithm a secret to prevent excessive manipulation of the system.  Furthermore, these factors change over time, so a strategy that was useful in 2013 may not be as useful today.  Nonetheless, there are some general principles that most SEO experts agree upon as a baseline for getting a page to rank well.

A few things to keep in mind:

1. **Search engines rank pages, not sites.**  While there are some aspects of your overall site that contribute to ranking such as domain authority and navigational structure, in the end Google ranks individual pages, not entire sites.  (This principle is fudged a bit when it comes to branded searches and sitelinks, in which case Google may choose to feature links to other pages on your site in addition to the index page in the search results).
2. **Page ranking is highly context-specific.**  In addition to the actual search terms used, Google takes everything else they know about the user into account when generating the search results page.  This includes the user's location, past browsing behavior, and device/browser properties - and probably a whole slew of other data that they can associate with a "signed in" Google user.  You need to put yourself into your target audiences' shoes when considering how to optimize your site so that Google will rank it _for those users_.  **At the same time**, you should always test your site rankings in "private browsing" mode so that your own Google behavior won't influence the results you see.  A page that you built and visit regularly might rank very high for you, but not for everyone else!
3. **This discussion is all about "organic" (unpaid) search results.**  Paid search engine results such as Adwords are a whole other animal.  There is some synergy though, but we highly encourage you to optimize your _organic_ results before considering paid links.  Also keep in mind that a growing percentage of people are using adblocking tools.
4. **Ranking takes time and patience.**  Don't expect your pages to begin ranking overnight!

We'll go over the major things to keep in mind here, and talk about how this fits into the architecture of UserFrosting (where appropriate):

## Page content

This is perhaps _the most_ important thing to focus on if you want a page to rank.  Modern search engines have sophisticated algorithms that go beyond [keyword counting](https://en.wikipedia.org/wiki/Tf-idf) in determining how relevant a page is to a user.  The three main things to keep in mind are:

1. **Unique** content
2. **Lots of words**
3. **Relevant** keywords

Google can tell when you're copying content from another page.  When it sees copied content, it decides that your page doesn't really contribute anything new to the list of results it will be showing to the user.  Just like in the movies where a staffer presents three different manila envelopes to a president, each representing a different course of action, Google wants to give its users as **rich a set of options as possible** for their query.  It wants to give them options A, B, and C - not options A, A, and A.

Having lots of words on a page gives Google more to work with when it determines how relevant that page is.  While you may not _need_ a lot of words to get your point across, it signals to search engines that your pages' contents have real value.  At the same time, you need to avoid [keyword stuffing](https://en.wikipedia.org/wiki/Keyword_stuffing) - putting an unnatural number of keywords in your content in an attempt to game the ranking algorithm.  This used to work in the past, but nowadays search engines will detect and penalize you if every other sentence is "Garden shed painting."

![Real world SEO](/images/real-world-seo.png)

To summarize, you need to write the text of your pages so that they have a healthy balance of your target keywords without appearing overly spammy.  You _do_ know what your target keywords are, right?

### Page structure

As it turns out, _where_ you put your content is also important.  Search engines pay attention to your page title (as in, what goes in the `<title>` tag) more than anything else.  Your page title should not only say _who_ you are, but _what_ you are as well.

**Bad**

`Mama's Kitchen`

**Good**

`Mama's Kitchen | Authentic Korean barbecue and restaurant in Bloomington Indiana`

Notice how we follow the name of the restaurant with a **brief** tagline that tells us what and where the place is.  This lets us target people who are searching for things like "Authentic Korean restaurant", "Korean barbecue Bloomington Indiana", "Korean restaurant Bloomington", etc.

These same rules also apply to other "header" information on your page such as the `<meta name="description">` tag contents and the header tags (`<h1>`, `<h2>`, etc).

## Page speed

If "content is king," speed is the queen.  Pages that are slow to load will rarely rank well.  This is especially critical in ranking on mobile devices, where bandwidth is limited and users have less patience.  You can see what Google thinks about the relative speed of a page using their [PageSpeed Insights](https://developers.google.com/speed/pagespeed/insights/) tool.

While lots of factors influence page loading speeds, we'll go over the biggest problems that we see over and over in sites that are struggling to rank.

### Optimize images

I can't even tell you how many times I've seen a 3MB logo image slapped hastily on a website.  Don't use a massive 3000px x 1000px image as your logo, only to "scale" it to 300 by 100 using the `width` and `height` properties of your `img` tag.  Your users' browsers will still end up downloading the 3000x1000 image with its massive file size, considerably slowing the time it takes to completely render the page.

Make your image files exactly the size at which you intend to display them (in some cases, you may want multiple different sizes for different devices) using an image editor.  Use Photoshop or a web-based tool like [Compressor.io](http://compressor.io/) to apply a lossless or reasonably lossy compression to further reduce the file size.

### Use compiled assets in production

The way UserFrosting [serves raw assets](/asset-management/basic-usage) is great for development and debugging, but not so much for production.  Each asset comes with the usual [round trip overhead](https://en.wikipedia.org/wiki/Handshaking#TCP_three-way_handshake) of an HTTP request.  On top of that, raw assets are served through the underlying Slim application, which adds a considerable amount of overhead with each request.  Finally, the asset files themselves (Javascript and CSS) are larger than they need to be to perform their function.

Using UserFrosting's `uf-bundle-build` and `uf-bundle` commands solves all three of these problems:

1. It **minifies** your Javascript and CSS files, making files smaller by making variable names shorter and removing comments and whitespace;
2. It **concatenates** Javascript and CSS files, reducing the number of requests needed by the page;
3. It copies the assets to the `public/` directory, so that they can be served directly by your webserver instead of going through the Slim application lifecycle.

### Caching

Caching should happen on a number of levels throughout your application, on both the server and client sides.  On the server, UserFrosting automatically caches route resolutions and fully-rendered Twig templates when you use the `production` configuration profile.  You can also configure the webserver itself to cache entire responses.  For example, see [nginx's caching documentation](https://www.nginx.com/resources/admin-guide/content-caching/).

Caching can also happen in the client's browser.  For example, you don't want the client to have to retrieve images and Javascript files each time they visit your page, if those assets haven't changed since their last visit.  Browser caching is handled by the `Cache-Control` response header, which is the server's way of telling the client's browser how long they should cache the response of a particular request.

Setting the value of the `Cache-Control` header is also typically handled directly by the webserver (though of course you could set this in your application codebase instead).  The webserver configuration files provided with UserFrosting contain some common default directives for configuring this behavior on your server.

The main issue with client-side caching is that you need some way of forcing the browser to refresh cached assets when they have changed (for example, when the site developers add a feature, fix a bug, or change some content).  This is called **cache busting**, and the most common approach is to simply change the name of the asset so that browsers assume that it is a new resource to be loaded.

Fortunately, UserFrosting's build tools take care of this as well.  Each time you compile your assets, a random hash is used to name the compiled files.  References to these assets in your pages are automatically updated to reflect these new names.

>>>>>> There are other steps that can be taken to further improve page performance, such as deferring the loading of Javascript and CSS, and inlining "above-the-fold" CSS.  Google has recently released its [Pagespeed webserver module](https://developers.google.com/speed/pagespeed/module/) for Apache and nginx, which can automatically perform optimizations like these automatically and behind the scenes.  We highly recommend that you look into installing and configuring this module if you use a supported webserver.

## Link building

In the past, a common SEO strategy was to simply get as many links from other websites to your page as possible.  This is because of Google's characteristic [PageRank algorithm](https://en.wikipedia.org/wiki/PageRank), which determines the importance of a page based on the number of pages that link to it, and _their_ PageRank as well.

This algorithm was easy to game though, and "link farms" developed to artificially boost the ranking of pages with purchased and often irrelevant links.  As a result, the relative importance of PageRank in search engine results has diminished over time.

Nowadays search engines still take links into account, but they are careful to exclude (or even penalize) "spammy" links.  Furthermore, the _quality_ of a link is more important than the quantity of links.  Links to pages act like letters of recommendation, and Google is only going to take a "letter of recommendation" seriously if it comes from a reputable source and is contextually significant.  In determining the relevance of a link, Google considers:

1. The [domain authority](https://en.wikipedia.org/wiki/Domain_Authority) of the linking site;
2. The [anchor text](https://moz.com/learn/seo/anchor-text) of the link;
3. The relevance of the linking page's content to the content of your page;
4. The prominance of the link on the linking page (position, number of other links, etc). 

All in all, this means you should focus on getting **good** links to your site, rather than **many** links.

## Summary

Page content, speed, and links are the Big Three in any search engine optimization strategy.  There are many other factors as well, such as structured content, geographic factors, social media presence, user engagement, and security which we won't go into here.  You should regularly **monitor and record** your site's search engine rankings, and don't be afraid to frequently experiment with your content to discover what improves your rank!

Finally, we recommend setting up Google Analytics and [Google Search Console](https://www.google.com/webmasters/tools/home) (formerly Webmaster Tools) to monitor your site's traffic and search ranking performance.  You can set your Google Analytics identifier in your Sprinkle configuration file with the `site.analytics.google.code` setting, and UserFrosting will automatically generate the tracking code for all of your pages.
