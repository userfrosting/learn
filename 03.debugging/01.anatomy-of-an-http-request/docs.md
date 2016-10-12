---
title: Anatomy of a Web Application
taxonomy:
    category: docs
---

*User types `http://www.owlfancy.com/health` into their browser's search bar*

**Your browser:** "Hi, my name is 74.125.70.102.  I'm a Chrome browser, version 53.0.2785.116, running on OSX, and blah blah blah (insert a bunch of other stuff about me).  Can I please have whatever's at `http://www.owlfancy.com/health`?"

**owlfancy.com:** "Sure, have some HTML.  The status code is 200.  Let me know if you need anything else.  Bye!"

**Your browser:** "Hmm, according to this, I'm supposed to ask you for `jquery.js`, `bootstrap.js`, `pellet.js`, `bootstrap.css`, and something called `/images/preening.jpg`.  Can I have those as well please?  I'm also supposed to get `https://fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic`, but that's not your problem.  I can ask `fonts.googleapis.com` myself.

**owlfancy.com: (for each requested item):** "Sure, here you go.  Let me know if you need anything else.  Bye!"

**Your browser:** "Ok, now I'll run all this CSS and Javascript code."  *Runs jquery.js, bootstrap.js, and pellet.js*

**Your browser:** "Hmm, looks like I'm supposed to give these boxes over here a border and shadow, change the font for this to Lora, and set it up so that when someone clicks "Forage", a picture of an owl swoops across the screen.  Oh, it also wants me to tell Google Analytics and Facebook what I just did.  Sure, after all my owner didn't tell me *not* to do that...I'll just send that information right now.

**Your browser:** "La la, waiting for my user to do something..."

*User clicks "Forage"*

**Your browser:** "Hey, someone just clicked 'Forage' - I'm supposed to do something now!  Let's see.  First I need to get a picture of an owl...excuse me, owlfancy.com?  Can I have `http://www.owlfancy.com/images/swoop.jpg`?"

**owlfancy.com:** "Sure, just a second.  Here you go! (Hands your browser an image file) The status code is 200.  Let me know if you need anything else.  Bye!"

**Your browser:** "Thanks!  I'll just put this picture right here and then make it swoop across the screen.  Wow!"

**Your browser:** "La la, waiting for my user to do something else..."

*User clicks "Account"*

**Your browser:** "Oh hi, owlfancy.com?  Can I have whatever's at `https://www.owlfancy.com/account`?"

**owlfancy.com:** "Uhh, who the heck are you?  Go ask for `https://www.owlfancy.com/login` instead.  The status code is 302.  Let me know if you need anything else.  Bye!"

**Your browser:** "Ok, may I please have `https://www.owlfancy.com/login` then?"

**owlfancy.com:** "Sure, have some HTML and some more Javascript.  The status code is 200.  Let me know if you need anything else.  Bye!"

**Your browser:** "I'll just show this to the user.  Hmm, looks like a form of some kind.  When they click 'Submit', I'm supposed to take whatever they put in this form and POST it to `https://www.owlfancy.com/login`.  Good thing they're using HTTPS, or someone else on my network might see my user's password!"

*User types in 'VoleALaMode' for their username, and 'hunter2' for their password.*

*User presses 'submit'.*

**Your browser:** "Excuse me, owlfancy.com?  I have something addressed for `https://www.owlfancy.com/login`.  It says 'username: VoleALaMode, password: hunter2'.

**owlfancy.com:** "Sure thing, I'll see if I can find that user... (checks the database) Sorry, I didn't find anything for that combination of username and password!  Here, have an error message: 'Your username or password is invalid.'  The status code is 400.  Let me know if you need anything else.  Bye!"

**Your browser:** "Well that didn't go well.  I guess I'd better break the news to my user.  Hmm, according to the Javascript on this page, I'm supposed to show the user this error message in this box over here.  I'll just go ahead and do that."

**Your browser:** "La la, waiting for my user to do something else..."

*User changes the username to 'Vole**s**ALaMode' and presses submit again*

**Your browser:** "Excuse me, owlfancy.com?  I have something addressed for `https://www.owlfancy.com/login`.  It says 'username: VolesALaMode, password: hunter2'.

**owlfancy.com:** "Sure thing, I'll see if I can find that user... (checks the database) Found her!  Here, have a success message: 'Welcome back, VolesALaMode!' Also, take this special code: 'nabddsXGa4FK0JHCipeEnAVXy8'.  Just show this to me with any other requests you make, and I'll know who you are. The status code is 302.  You should probably ask me for `https://www.owlfancy.com/account` next.  Let me know if you need anything else.  Bye!"

**Your browser:** "Special code, eh?  I'll just add this to my cookies for this site and send it back with any more requests I end up making.  I'm sure that's what she wants me to do!  Ok, now I'm supposed to ask for `https://www.owlfancy.com/account`..."

**Your browser:** "Excuse me, owlfancy.com?  Can I have `https://www.owlfancy.com/account`?  I also have this special code.  It's 'nabddsXGa4FK0JHCipeEnAVXy8'."

**owlfancy.com:** "Sure thing.  Hey, I know you, you're VolesALaMode!  (erm, at least I hope it's you and not someone who stole your secret code!)  Let me go look up a few things for you...Hmm, I'm supposed to get a list of your next HootMeets from the database, whatever a HootMeet is...ok, found it.  I'll just lay them out in this nice HTML template and send it back.  Here, have this HTML!  The status code is 200.  Let me know if you need anything else.  Bye!"

**Your browser:** "Thanks!  I'll just show my user this..."

*Browser now shows `https://www.owlfancy.com/account`*

**Your browser:** "There's some Javascript here too, so I'll just run that.  Hmm, it's telling me that I should ask for `https://www.owlfancy.com/account/notifications`.  Hi owlfancy.com, can I have that please?  I also have this special code.  It's 'nabddsXGa4FK0JHCipeEnAVXy8'."

**owlfancy.com:** "Sure thing.  Hey, I know you, you're VolesALaMode!  Here you go, have some JSON.  The status code is 200.  Let me know if you need anything else.  Bye!"

**Your browser:** "Ok, what am I supposed to do with this?  I'll just look back to the Javascript for this page.  Let's see, it wants me to display these notifications with a green background and a light shadow over here, just below the navigation bar.  Can do!"

