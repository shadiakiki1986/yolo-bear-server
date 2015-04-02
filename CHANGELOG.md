# V 0.0.0
## 2015-04-02
* allowing empty nicknames
* dropStale was not properly filtering for unprotected nicknames
* putEmail now also puts peerId (to show which nicknames are verified by emails)
* listNick now merges the nicknames with the verified email addresses

## 2015-04-01
* added email/password to protect a nickname... have yet to test this
* split out nickname from email registration
** took me all day to realize how bad of a design that was

## 2015-03-31
* putNick.php was changed to POST
** was very tricky to get client-side angular post work with php angular post and cors enabled

## 2015-03-30
* added yolo-bear-users table in dynamodb + putNick.php to api + dropStaleNick.php to scripts
* added listNick.php

## 2015-02-28
* moved documentation for running peerjs server from yolo-bear to yolo-bear-server
* first implementation of api
