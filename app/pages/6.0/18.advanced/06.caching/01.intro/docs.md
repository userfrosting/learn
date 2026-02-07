---
title: What, Why, and When?
description:
    metadata: This section explains the motivation and rationale behind caching, and presents several scenarios where caching can help improve the performance of your server and application, as well as improve user experience.
wip: true
---

PHP scripts are executed at run time. This means that every time someone refreshes a page from your website in their browser, all the code defined in your app needs to be interpreted by the PHP engine. Modern hardware and processing power means that even complex PHP scripts can be executed in seconds. However, this is not _always_ the case, especially when dealing with external data.

In some cases, those external services can add **minutes** to the script execution and cause the client's browser to time out, thus blocking a page from loading and being completely displayed. This creates a bad user experience. Even if you show the user a fancy loading animation, any load time longer than a couple of seconds will create frustration for the user and prompt her to quit your page, or worse, reload the page, starting the process all over again.

This is where caching comes along. Caching makes it possible to store **processed information** in a **fast medium** - most of the time, in memory.

In an ideal situation, you can make simple improvements in your code to reduce the time needed for a script to execute. In some other situation, you don't have control over what's taking so long. Let's look at some examples:

## Complex and/or large data manipulation

It can sometimes take a while to manipulate a large quantity of data. While databases are good at this in most cases because of the way the data is structured inside the database, it's not the case when dealing with **text files**. It's like _finding a needle in a haystack_.

In such a situation, two approaches can be used to make the process of finding that needle faster and more efficiently. It mostly depends on whether the needle will always be at the same place in the haystack or if its position is always different.

### Position is always the same

If the position of the needle is always the same, you can use cache to keep track of **the needle location** or to keep a trace of **the needle itself**. If the result you want in the end is the location of the needle **and** there is a potential that the needle will change every time while being at the same place, then the location of the needle should be saved. Otherwise, if for example you want the color of that needle, and you know it will always be the same color, then you can save the color itself in the cache. When looking for the needle next time, you can check in the cache if it's there first before spending a considerable amount of time searching for it.

### Position change over time

If the position of the needle change every time, then you may be out of luck. It depends on the **frequency** with which the needle switches places. If it moves every 10 minutes, you could cache the location of the needle for a short period of time. If 4 people ask where the needle in the next 5 seconds, chances are good that it's still at the same place. You don't want to look for 4 times for nothing.

If the needle position changes every time or at a very fast frequency, you may be out of luck and have to search for it every time. Various techniques can still be used to make the process faster, but in that case cache probably won't be very helpful - unless knowing the position of the needle is not what's actually important. Even if the needle is constantly moving, maybe the material it's made of or its color won't change overtime. This can be stored in the cache!

## Large database queries

Databases are good at finding things...most of the time. A common operation is to search for specific row(s) based on the value of one or many columns. Even with then of thousands of rows to search, modern database engines are extremely efficient.

However, when faced with more complex operations based on a large cell value or heavy intermediate calculations, databases can be slower. For example, databases tends to be slower when finding a word in a large text block (which is a special case solved by using _search indexes_), joining multiple tables together or sorting/filtering based on calculated values or intermediate results.

This last one is usually the costliest one in terms of performances. The perfect example is a **team leaderboard**. Imagine a situation where you want to display the names of the players with the most achievements for team Blue and team Red. To get the top 5 players on each team, the typical algorithm would be :

1. For each user, determine if the achievement is acquired or not (by looking at other tables, etc.);
2. Calculate the number of achievements each user has;
3. Sort the users by the total number of achievements;
4. Split the users per team;
5. Get the top 5.

The truth is, when dealing with a handful of users, achievements and teams, this will be pretty quick to handle if calculated for every request. But as your application grows, it will become slower and slower. Let's do the math :

| Number of users | Number of achievements | Number of teams | Number of achievements to look at | Total number of leaders |
| --------------- | ---------------------- | --------------- | --------------------------------- | ----------------------- |
| 25              | 5                      | 2               | 25 * 5 = 125                      | 2 teams * 5 = 10        |
| 500             | 20                     | 3               | 500 * 20 = 10 000                 | 3 teams * 5 = 15        |
| 10 000          | 150                    | 10              | 10 000 * 150 = 1 500 000          | 10 teams * 5 = 50       |

Access and count 125 entries? No problem. Access and count **1 500 000 entries**? _Ouch..._

This problem can sometimes be solved in the database itself: the intermediate calculation can be stored in a new table in the database instead of being calculated every time. For example, every time a user acquire an achievement, you can mark it as `done` in a table, update the user table with the new number of achievements, update the team table with the new leaders, etc. While it's easy for this example, it may not always be the case.

That's why another approach is to do all of those calculations once and store the result in the cache. By doing so, you don't even need to involve the database. The result is already available. In some situations, caching the result can not only be faster, but also be easier as intermediate tables can be hard to implement or keep up to date. Even when storing intermediate results in the database, the cost of displaying 5 leaders per team can grow very fast. With 25 users, getting 10 leaders is pretty easy. But to get 50 leaders out of 10 000 users, you still have to look through 10 000 rows only to find 50...

As with the _needle in a haystack_ problem, the **frequency** at which the data is updated must also be taken into account. If the 10 000 users get a new achievement every 6 months, that leaderboard won't be updated as often as if the leaderboard is constantly moving.

## External APIs

External APIs can benefit the most from caching. There are two rules here : External APIs **can be unreliable** and **you don't want to query them uselessly**.

Since you usually don't control them, fetching data from an external API (another server or service outside your own app) can be the most difficult part to optimize. If an API takes a minute to return something, there's pretty much nothing you can do beside wait...and then cache the result. Again, you have to take into account the frequency that data will change - this may or may not be the best solution. If the API data changes every 5 seconds, caching becomes less useful than if it changes once in a while.

If an API is slow, another approach is to return the cached result as soon as the user needs it and fetch new data in the background (using cron job or similar). Users are then presented the most up-to-date values **you** have and don't suffer the lag generated by that slow API.

You also don't want to hit the API at every request. Not only might it use your own resources and bandwidth to access an external API, it also uses the other server resources. If you query the API too frequently (hit per seconds), the server may throttle or block you. Again, if you know the API data only changes once per hour, there's no need to ask for new data every time you want to use it. Check back once in a while and cache the result.

## Reading and writing to the file system

While reading and writing to the file system can be fast on modern hardware, especially considering modern SSDs, these operations can be made even faster when using memory based drivers. Nothing can be faster than storing information in the RAM!
