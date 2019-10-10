# HOA Finance

This is a crappy quick hack I did when I got voluntold into the treasurer role
of my condominium's HOA board back in, like, 2009 or something. I never got
around to properly opensourcing it or making it configurable, and it's in an
incredibly rough state.

As I recall, here is what it supported at the time:

* MySQL database (presumably with a hand-deployed schema, oops)
* Very basic accounting of dues and reimbursements from an arbitrary number of
  condo units
* A UI which only scales to, like, 8 units (which happens to be the number of
  units which were in the condo)
* The ability to produce monthly and annual expense reports/balance sheets/etc.

Mostly it was less-bad than the terrible Excel spreadsheets that I'd inherited
from the previous treasurer, and it made it a lot easier for me to keep track of
a couple of chronically-delinquent unit owners' balances.

Eventually I made the case to switch to an external management company which
took care of all this stuff for us, and it is what I would *highly* recommend
for anyone else in this situation.

This system would probably also be usable for other multi-person accounting
situations such as clubs with dues, academic labs with fees, etc.
