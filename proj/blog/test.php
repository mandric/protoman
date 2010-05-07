<?php

// Testing array-based prepopulation of fields
$x = new Post(array("title" => "Hi there, Ima Post!", "body" => "oooofda"));

$x->save();

$x->summary = "Summarily summary.";

$x->id;
1

unset($x);

$y = new Post(1);

// ->id won't be populated if the load fails
$y->id;
1

$y->summary;
Summarily summary.

count($y->comments);
0

$c = new Comment();

$c->name = "Chuck Norris";

$c->body = "ROUNDHOUSE KIIIICK /yuo";

// This really ought to test against true, which is what the ORM returns, but 1 is the value eval() gives...
$c->save();
1

// Many-to-many; saved automatically
$y->comments[] = $c;

unset($y);

$z = new Post(1);

count($z->comments);
1

$z->comments[0]->name;
Chuck Norris

$z->comments[0]->delete();

count($z->comments);
0
