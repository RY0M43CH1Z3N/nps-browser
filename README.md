# NoPayStationBrowser
NoPayStation Browser for PSVITA

## NOTE: This is a project I did for fun. I have made sure not to include any links or details on files from NoPayStation. This repository only includes game details from GamesDB site. Using .tsv file from NoPayStation is up to you.

## Add NoPayStation Link (at your own risk!)
1. Open `src/middleware.php` and add link to NoPayStation `.TSV` file where it says `[[NOPAYSTATION LINK GOES HERE]]`
2. Make sure `api-cache.json` and `settings.json` have write access `$ chmod 777 api-cache.json` and `$ chmod 777 settings.json`
3. Finally, host it locally or anywhere you think best. You can also use ngrok.io to access your local site on your vita.
4. This web app uses SlimPHP and caches the TSV file as an array so that access is faster. You can change the expire time for cache in `src/middleware.php`.
5. App shows 10 items at a time and is accessed as `url/[page number]`

## TODO: App uses Bootstrap 4 and it is upto you to make the UI look good :)

## Screens

![alt text](https://github.com/anup756/nps-browser/blob/master/src/img.png)

