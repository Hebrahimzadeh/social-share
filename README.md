# Share

**Share links with Laravel**
 
 
## Services available

- Blogger : blogger
- Digg : digg
- Email : email
- Evernote : evernote
- Facebook : facebook
- Gmail : gmail
- LinkedIn : linkedin
- Pinterest : pinterest
- Reddit : reddit
- Scoop.it : scoopit
- Telegram.me : telegram
- Tumblr : tumblr
- Twitter : twitter
- vk.com : vk


## Installation

Install Composer dependency into your project

    composer require hebrahimzadeh/laravel-share

## Usage

Get a link (example with Twitter)
```php
Route::get('/', function()
{
    return Share::page('http://www.example.com', 'My example')->twitter();
});
```

Returns a string :
```link
https://twitter.com/intent/tweet?url=http%3A%2F%2Fwww.example.com&text=Link+description
```

Get many links
```php
Route::get('/', function()
{
    return Share::page('http://www.example.com', 'Link description')->services('facebook', 'twitter')->getLinks();
});
```

Returns an array :
```json
{
    "twitter" : "https://twitter.com/intent/tweet?url=http%3A%2F%2Fwww.example.com&text=Link+description",
    "facebook" : "https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2Fwww.example.com&title=Link+description"
}
```

Get ALL the links
```php
Route::get('/', function()
{
    return Share::page('http://www.example.com', 'Link description')->services();
});
```
Returns an array of results for all defined services.

## Customization

Publish the package config:
```php
php artisan vendor:publish --provider='Hebrahimzadeh\Share\ShareServiceProvider'
```
Add a new service in config/social-share.php:
```php
'mynewservice' => [ 'view' => 'share.mynewservice' ]
```

Add Blade templating code in *share.mynewservice* view file to generate a URL for *mynewservice*. You have access to:

- service - the service definition (shown above).
- sep - separator used between parameters, defaults to '&amp;'. Configurable as *social-share.separator*.
- url - the URL being shared.
- title - the title being shared.
- media - media link being shared.

Example:

    https://mynewservice.example.com?url={{ rawurlencode($url) }}<?php echo $sep; ?>title={{ rawurlencode("Check this out! $title. See it here: $url") }}

Another example for the *email* service. Change the service config to be *[ 'view' => 'whatever' ]* and put this in the view file:

    mailto:?subject={{ rawurlencode("Wow, check this: $title") }}<?php echo $sep; ?>body={{ rawurlencode("Check this out! $title. See it here: $url") }}

Localizing? Easy, use Laravel's trans() call:

    mailto:?subject={{ rawurlencode(trans('share.email-subject', compact('url', 'title', 'media'))) }}<?php echo $sep ?>body={{ rawurlencode(trans('share.email-body', compact('url', 'title', 'media'))) }}

Create a file at resources/lang/en/share.php with your choice of subject and body. URLs arguably have a maximum length of 2000 characters.

Notice the use of *<?php echo $sep; ?>*. It's the only way to print out an unencoded ampersand (if configured that way).

## Upgrades

When the package is upgraded, changes to the config and views should be republished into your project:

    php artisan vendor:publish --provider='Hebrahimzadeh\Share\ShareServiceProvider'

Use source control to work out what has changed if you have customized the files.
