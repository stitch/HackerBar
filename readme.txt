== About ==

HackerBar

Based upon Hack42 bar by BugBlue. Rewritten by Stitch / Elger Jonker in a modular PHP/MySQL application with some new and different features.


Commercial support available. This is open source software, based on other open software and non-commercial usage licences. Some parts of this
program requires a commercial licence for commercial applications. Consult the below 3rd party software list before using commercially or consult.


This software uses the following third-party components:
- Requests library by rmccue 	https://github.com/rmccue/Requests
- pChart2.1.4 by 		http://www.pchart.net/ (only for non-commercial use)
- meekrodb by 			http://meekro.com/quickstart.php
- Datagrid by... for JQuery <source cannot be located at time of writing, sorry>
- jQuery
- Sounds are taken from various sources, meant as a sample and should be replaced by your own.

Developed with:
- PhpStorm 	https://www.jetbrains.com/phpstorm/
- Xampp 	https://www.apachefriends.org/index.html
- SQLeo Visual Query Builder http://sourceforge.net/projects/sqleo/
- Fiddler 	http://www.telerik.com/fiddler


== Feature list and more ==

Features:
- Barcode-input support
- Management of products via phpmyadmin interface
- Deposit money
- Account information
- keyboard, touch and mouse input
- cancel anything at any time
- undo transactions
- Multi monitor support
- Plugin architecture with a lot of freedom.


Additional plugins
- Soma FM radio plugin
- Soundboard
- Instant buying of popular products in account, with update of account information (is a hack)
- Restocking of products
- Database: added unlisted products and product.purchasable field (to hide products from the store, but still lets you sell them)
- Purchase of unlisted products by just entering a value in cents
- add Spacestate switch button
- Support multiple monitors
- add a series of CSS classes to the command button...
- view charts of the past weeks of the most popular poducts of the last month

improvements:
- Layout: static top menu, better for browsers, awesome for smartphones.
- Layout: when there is no left or right sidebar, plugins can use the entire display area (more sounds on the soundboard)
- Quick searching while you type
- shop: heuristics to support faster adding of products by keyboard (you can be more clumsy)
- added images to some products
- automatic table of contents with help
- improved the looks of the help plugin
- Undo now has a period of 1 week. (can be a lot of transactions(!))
- Separated account information from account management
- longer chart-lists for the shop.

Bugfixes:
- bugfix: casing accountnames
- bugfix: rounding of financial data
- play audio in firefox
- removed the hackerbar database name from views, so you can rename your schema and work with dumps
- monthly charts now work.



== Installation ==

Prerequisites:
MySQL with IPV6 support. For example versoin 5.6.21
PHP 5.6.3 or better
Chrome or chromium
For some plugins the X-Frame Header should be stripped by the browser (Spacestate, youtube and Soma FM), there are plugins to do this and make your browsing experience less safe.


1: setup a database. Use the scripts in the /datamodel/ folder, this also contains a sample dataset to experiment with.
There are about 12 tables and 12 views.

2: The database credentials can be altered in the /classes/meekrodb.2.3.class.php file. Hackerbar should have a dedicated user. Special permissions are in the MySQL Workbench file, which are not exported unfortunately.

3: Drag and drop the files somewhere in the webroot. For example in the folder /hackerbar/

4: Start your browser and browse to /hackerbar/index.html

5: Open a second window and browse to /hackerbar/AccountScreen.html

6: This is an AJAX application. Debugging errors is most easily done with a proxy like Fiddler, Burp or Zap.