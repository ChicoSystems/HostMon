HostMon
=======
*This Project is in a pre-alpha state.*

Hostmon is a multi-user latency &amp; uptime analytical tool.

Manual Install Instructions
-Dependencies
--Web Server
---PHP

--DB Server
---MySQL

--Backend Server
---Java 6-8

-Backend
--needs java to run, or jdk to compile.
--needs mysql-connector in the classpath
--When compileing add to the classpath javac -cp .:mysql*.jar HelloWorld.java
*.
--Then run with javaa -cp .:mysql*.jar
.*

-MySQL
--create database ;
--import db from .sql dump ;
--create user ;
--give user permissions on db ;
--edit db.cfg with known parameters ; 


Version 0.6 (7/7/16)
-Added tour system for new users.
-Fixed login bug. Enter button now works.

Version 0.5
-Added backend online display dot to grid.php
-Added ability of admin to stop or start the backend via the front end.
-Made sure everything works on windows xampp and linux lamp.

Version 0.4
-Added Installation System
-Got rid of menu access on device.php

Version 0.3
-Fixed auto color updating bug in grid.php.
-Fixed graph NaNing out bug in device.php.
-Secured and finished login system.
-Added Version Display.

Version 0.2
-grid.php is now working.
-Added config/menu section.
-Backend successfully ran for 100 days, 16 million pings, no issues detected.

Version 0.1
-Added ability to add new device. Not done yet.
