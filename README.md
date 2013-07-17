Jasny's Twig Extensions
=======================

A number of useful filters for Twig.

## Installation ##

Jasny's Twig Extensions are registred at packagist as [jasny/twig-extensions](https://packagist.org/packages/jasny/twig-extensions)
and can be easily installed using [composer](http://getcomposer.org/). Alternatively you can simply download the .zip and copy
the file from the 'src' folder.


## Date extension ##

Format a date base on the current locale. Requires the [intl extension](http://www.php.net/intl).

* localdate     - Format the date value as a string based on the current locale
* localtime     - Format the time value as a string based on the current locale
* localdatetime - Format the date/time value as a string based on the current locale
* age           - Get the age (in years) based on a date

```php
Locale::setDefault(LC_ALL, "en_US"); // vs "nl_NL"

$twig = new Twig_Environment($loader, $options);
$twig->addExtension(new Jasny\Twig\DateExtension());
```

```
{{"now"|localdate(long)}}                   <!-- July 12, 2013 --> <!-- July 12, 2013 -->
{{"now"|localtime(short)}}                  <!-- 5:53 PM --> <!-- 17:53 PM -->
{{"2013-10-01 23:15:00"|localdatetime()}}   <!-- 10/01/2013 11:15 PM --> <!-- 01-10-2013 23:15 -->
{{"22-08-1981"|age}}                        <!-- 31 -->
```


## PCRE ##

Exposes [PCRE](http://www.php.net/pcre) to Twig.

* preg_quote   - Quote regular expression characters
* preg_match   - Perform a regular expression match
* preg_get     - Perform a regular expression match and returns the matched group
* preg_get_all - Perform a regular expression match and return the group for all matches
* preg_grep    - Perform a regular expression match and return an array of entries that match the pattern
* preg_replace - Perform a regular expression search and replace
* preg_filter  - Perform a regular expression search and replace, returning only matched subjects.
* preg_split   - Split text into an array using a regular expression

```php
$twig = new Twig_Environment($loader, $options);
$twig->addExtension(new \Jasny\Twig\PcreExtension());
```

```
{% if client.email|preg_match('/^.+@.+\.\w+$/) %}Email: {{ client.email }}{% endif %}
Website: {{ client.website|preg_replace('~^https?://~')
First name: {{ client.fullname|preg_get('/^\S+/') }}
<ul>
  {% for item in items|preg_split('/\s+/')|grep_filter('/-test$/', 'invert') %}
    <li>{{ item }}</li>
  {% endfor %}
</ul>
```


## Text ##

Convert text to HTML + string functions

```php
$twig = new Twig_Environment($loader, $options);
$twig->addExtension(new \Jasny\Twig\TextExtension());
```

* paragraph - Add HTML paragraph and line breaks to text
* more - Cut of text on a pagebreak
* truncate - Cut of text if it's to long
* linkify - Turn all URLs in clickable links (also supports Twitter @user and #subject)
* split - Split text into an array (explode)
