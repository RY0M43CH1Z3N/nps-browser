<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

/**
 * API Request Caching
 *
 *  Use server-side caching to store API request's as JSON at a set
 *  interval, rather than each pageload.
 *
 * @arg Argument description and usage info
 */
$pages=0;

function json_cached_api_results($page = 1) {
    global $request_type, $purge_cache, $limit_reached, $request_limit;
    $cache_file = dirname(__FILE__) . '/api-cache.json';
    $settings_file = dirname(__FILE__) . '/settings.json';
    $settings = unserialize(file_get_contents($settings_file));
    if(empty($settings)){
      $expires = time() + 2*60*60;
      $setting = array();
      $setting['expires'] = $expires;
      file_put_contents($settings_file, serialize($setting));
    }else{
      //$setting = json_decode(json_encode(json_decode(file_get_contents($settings_file))), True);
      $expires = $settings['expires'];
    }
    // Check that the file is older than the expire time and that it's not empty
    if ( time() > $expires || empty(unserialize(file_get_contents($cache_file))) || $purge_cache) {
        $expires = time() + 2*60*60;
        $setting = array();
        $setting['expires'] = $expires;
        file_put_contents($settings_file, serialize($setting));
        $array_results = api_request();
        // Remove cache file on error to avoid writing wrong xml
        if ( $array_results ){
          file_put_contents($cache_file, serialize($array_results));
        }else{
          unlink($cache_file);
        }
    } else {
        // Check for the number of purge cache requests to avoid abuse
        //if( intval($_SESSION['views']) >= $request_limit )
        //    $limit_reached = " <span class='error'>Request limit reached ($request_limit). Please try purging the cache later.</span>";
        // Fetch cache
        $array_results = unserialize(file_get_contents($cache_file));

    }
    // paging
    $limit = 10; // five rows per page
    $offset = ($page - 1) * $limit; // offset
    $total_items = count($array_results); // total items
    $total_pages = ceil($total_items / $limit);
    $pages = $total_pages;
    $stageArray = array_splice($array_results, $offset, $limit);
    $vitagames = json_decode(file_get_contents(dirname(__FILE__) . '/gamesdb/platforms/psvitagames.json'));
    $finalArray = array();
    foreach($stageArray as $result){
        $ID = $result['Title ID'];
        $key = array_search($ID, array_column($vitagames, 'SID'));
        $vgame = $vitagames[$key];
        $xml=(simplexml_load_file(dirname(__FILE__)."/gamesdb/games/game-".$vgame->ID.".xml")) or die("Error: Cannot create object");
        $result['Release Date'] = (string) $xml->Game->ReleaseDate;
        $result['Overview'] = (string) $xml->Game->Overview;
        $result['Platform'] = (string) $xml->Game->Platform;
        $result['ESRB'] = (string) $xml->Game->ESRB;
        $result['Genres'] = get_object_vars($xml->Game->Genres);
        $result['Players'] = (string) $xml->Game->Players;
        $result['Publisher'] = (string) $xml->Game->Publisher;
        $result['Developer'] = (string) $xml->Game->Developer;
        $result['Rating'] = (string) $xml->Game->Rating;
        $images = get_object_vars($xml->Game->Images);

        if(array_key_exists('boxart', $images)){
          $result['Boxarts'] = $images['boxart'];
        }else{
          $result['Boxarts'] = '';
        }
        // if(array_key_exists('fanart', $images)){
        //   if(is_object($images['fanart'])){
        //     $result['Fanarts'] = get_object_vars($images['fanart']);
        //   }else{
        //     $result['Fanarts'] = $images['fanart'];
        //   }
        // }else{
        //   $result['Fanarts'] = '';
        // }
        // if(array_key_exists('screenshot', $images)){
        //   if(is_object($images['screenshot'])){
        //     $result['Screenshots'] = get_object_vars($images['screenshot']);
        //   }else{
        //     $result['Screenshots'] = $images['screenshot'];
        //   }
        // }else{
        //   $result['Screenshots'] = '';
        // }
        $finalArray[] = $result;
    }
    return $finalArray;
}

/**
 * Request jobs from Indeed API
 *
 * Split the request into smaller request chunks (25 results each)
 * and then consolidate them into a single array to meet the API
 * requirements.
 */
function api_request() {
    $headers = array('Accept' => 'text/tab-separated-values');
    $query = array();

    $response = Unirest\Request::post('[[NOPAYSTATION LINK GOES HERE]]', $headers, $query);
    return parse_tsv($response->body);
}

function parse_tsv($csv_string, $delimiter = "\t", $skip_empty_lines = true, $trim_fields = true){
  $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
  $enc = preg_replace_callback(
      '/"(.*?)"/s',
      function ($field) {
          return urlencode(utf8_encode($field[1]));
      },
      $enc
  );
  $games = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
  $header = array_shift($games);
  $headerValue = explode($delimiter, $header);
  $array = array();
  foreach($games as $game){
    $fieldValue = explode($delimiter, $game);
    if(count($fieldValue) == count($headerValue)){
        $array[] = array_combine($headerValue, $fieldValue);
    }
  }
  return $array;
}
