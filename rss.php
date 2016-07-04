<?PHP
  // define script parameters
  $BLOGURL    = "http://feeds.bbci.co.uk/news/rss.xml?edition=int";
  $NUMITEMS   = 2;
  $TIMEFORMAT = "j F Y, g:ia";
  $CACHEFILE  = "/tmp/" . md5($BLOGURL);
  $CACHETIME  = 4; // hours

  // download the feed iff a cached version is missing or too old
  if(!file_exists($CACHEFILE) || ((time() - filemtime($CACHEFILE)) > 3600 * $CACHETIME)) {
    if($feed_contents = http_get_contents($BLOGURL)) {
      // write feed contents to cache file
      $fp = fopen($CACHEFILE, 'w');
      fwrite($fp, $feed_contents);
      fclose($fp);
    }
  }

  include "rssparser.php";
  $rss_parser = new RSSParser($CACHEFILE);

  // read feed data from cache file
  $feeddata = $rss_parser->getRawOutput();
  extract($feeddata['RSS']['CHANNEL'][0], EXTR_PREFIX_ALL, 'rss');

  // display leading image
  if(isset($rss_IMAGE[0]) && $rss_IMAGE[0]) {
    extract($rss_IMAGE[0], EXTR_PREFIX_ALL, 'img');
    echo "<p><a title=\"{$img_TITLE}\" href=\"{$img_LINK}\"><img src=\"{$img_URL}\" alt=\"\"></a></p>\n";
  }

  // display feed title
  echo "<h4><a title=\"",htmlspecialchars($rss_DESCRIPTION),"\" href=\"{$rss_LINK}\" target=\"_blank\">";
  echo htmlspecialchars($rss_TITLE);
  echo "</a></h4>\n";

  // display feed items
  $count = 0;
  foreach($rss_ITEM as $itemdata) {
    echo "<p><b><a href=\"{$itemdata['LINK']}\" target=\"_blank\">";
    echo htmlspecialchars(stripslashes($itemdata['TITLE']));
    echo "</a></b><br>\n";
    echo htmlspecialchars(stripslashes($itemdata['DESCRIPTION'])),"<br>\n";
    echo "<i>",date($TIMEFORMAT, strtotime($itemdata['PUBDATE'])),"</i></p>\n\n";
    if(++$count >= $NUMITEMS) break;
  }

  // display copyright information
  echo "<p><small>&copy; {",htmlspecialchars($rss_COPYRIGHT),"}</small></p>\n";
?>