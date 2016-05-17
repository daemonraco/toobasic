# TooBasic: Author's Note
Well hi there, my name is Alejandro and I'm cyberly known as [DAEMon
Raco](http://www.daemonraco.com).
In this page I'll talk about __TooBasic__ and my motivations to create it and I'll
try to give some explanations to things you may be wondering... maybe not.

## TooBasic?!
Yes, the name is __TooBasic__, why? well, when I started I wanted to create a
simple framework I could paste somewhere in a _xampp_ or _Apache_ folder and start
typing some logic right away without the need to worry about _how I read the
current class to use from the url_.

In a manner of speaking I still think and feel that's the goal of this framework,
but if you read a little more about it you'll see it's not that _basic_.
Perhaps, in some point I had to ask my self _"Am I going too far for a basic
framework?"_, but I've never asked me that because all the stuff I'm adding are
things I want to have in my reach and in the reach of anyone who uses it
regardless of the goal of a site running over __TooBasic__.
I'll never know if I'll use this framework to display a simple welcome page at
some URL, a blog, a forum or even a social network, so I need to keep an open mind
and think that someday me or someone may want to use _Memcached_ instead of
_Redis_, or _SQLite_ instead of _MySQL_, or simply create a plugin to change the
skin of a welcome page.

It is too basic in a way, you create a small class in a specific place and that is
a service with an interface and a basic JSON response.
You set an instance variable to `true` and its response will be cached for an
hour, just like that.
But I'll agree if someone says _it's not that basic_.

## One action one controller?!
Yes, the answer is as plain and simple as that.
That's because this topic would be like the decision of placing a curly brace in
the same line or the next when you write an `if` sentence (python doesn't apply).
Anyway, I'll try to give some explanation on why I do this.

In my experience I've seen developers writing some not-so-simple logic inside a
controller's action, but they are smart enough to place the complex logic inside a
protected/private method inside that controller.
Then they need a new action and they create a new method for that action inside
the same controller.
Let's assume here both actions are related to the same _meaning_ managed by that
controller, otherwise we have nothing else to discuss.
Due to the smart move previously made, this new action can use that complex logic
and with a few touches the developer has a new action up and running.

Time passes and a wild new controller appears and it requires that same old
complex logic.
But come on, we can not duplicate code, that would be ugly, so the programmer
takes a ton of metallic grey duct tape and tucks them together so tightly they
start sharing that complex logic... NOOOOO!!!... cof... cof, let me... let me calm
down a little, I'll just take this jerrycan of gasoline AND BURN THE CODE TO
ASHES!!!
 
"_Wait!, calm down, what is the problem?_"

Well, it's WRONG!, wrong from the beginning, that complex logic is shouting for
its own place to live and be visited by those controllers.
That complex logic MUST be part of a model.
Believe me, there will be a third controller asking for that logic and for that
time there's gonna be so much duck tape and dust they'll need a paleontologist to
dig that piece of code out of there.

This kind of situations are those I want to mitigate by having separated
classes/controllers for each action.
At least in this way, that programmer will think twice about putting that logic
somewhere else.

"_But... what about shell tools?_"

Enough!, let's change the topic already.

## Engrish
As you may noticed, English is not my mother language, that's the reason way you
will find some many mistakes. Believe me, I'm trying to get better at it, but s**t
happens :D

## _Why query adapter is so complicated?_
I know I know, I wasn't expecting it to become so complicated and weird and I'll
try address this in the future, it is a pain even for me.

## PHPUnit
Yes, we have some [PHPUnit](https://phpunit.de/) test-cases stored in our
repository, but there may not be as many cases as you expect
_Why?_
Well the answer is rather simple, I don't have a testing team checking on every
thing that exist inside __TooBasic__ so I have to write every case.
But I can guarantee that I'll try to write as many cases as I can, it may take a
while and they may not be present in your current version, but at least I'll try.

<!--:GBSUMMARY:Appendix:2:Author's Note:-->
