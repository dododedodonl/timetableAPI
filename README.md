timetableAPI
============

A PHP API for converting online (HTML) timetables into JSON, XML and more. Currently only supports versions of the Untis timetable software.


## Using crawl.php

### How the script works
Righ tnow the script is a little messy, but this is how it is built:
- The $schools array contains all the info we need about schools to be able to get timetables from them. Most of the fields are optional, but `rooster_system`, `rooster_classes` and `rooster_url` are required.
  - `rooster_system` is a string constant telling the script which parser should be used. Current values are currently:
     - `untis2011`
     - `untis2011-r1`
     - `untis2012`
     - `untis2012-r1`
  - `rooster_url` is a variable containing the url of the default timetables. `%class%` will be replaced with the requested class or student number.
  - `rooster_classes` is the url of the page that lists all the classes or students.


### How to get information out of the script
The script works as a little API. These are the GET-parameters it supports:
- `school` - the school name you want information from (array key in $schools)
- `class` - the group or student number that you want to get the timetable for
- `format` - the format you want the information to be in: json, xml, txt, php (optional, default: json)
- `callback` - JSONP callback name (optional)

Example call:
```
http://www.example.com/crawl.php?school=grotius&class=A1C&format=xml
```

### With .htaccess
If you are using Apache, you can use the URL rewrites in htaccess.txt (by renaming it to .htaccess). URLs then look like:
```
http://www.example.com/grotius/A1C.xml
http://www.example.com/rombouts/RB1A.json
```

### Caching
The script uses a `temp` folder to cache timetables so that they load faster. Make sure this directory exists and is writable.

## Live example
You can test this script on http://rooster.mijnbc.nl/. You are allowed the API exposed there for your applications too, but please let us know. If you know any other schools that use Untis timetable software, we can add it to the API as well.

Example URL:
```
http://rooster.mijnbc.nl/rombouts/RB1A.json
```
