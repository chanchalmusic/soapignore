<?php
/**
 *  starts with base url as root
 *  if crawler halted in the middle, check last entry in the processed table
 *  use that last entry as new start point
 */
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

$dbh = new PDO('mysql:host=localhost;dbname=crawlcompare', 'root', 'xxxxxx');

// specify a base link to start crawl, no trailing slash
$base = "http://www.bettingexpert.com";

// in full url which will identify internal site
$siteIdentifier = 'bettingexpert.com';

// normal mode or restarted mode? 1 normal mode, 0 restarted mode
$mode = 0;

bootstrap($base, $siteIdentifier, $mode);

function bootstrap($base, $siteIdentifier, $mode) {
    global $dbh;
    //syslog(LOG_ALERT, "hi");
    echo strftime("%c"). "in bootstrap";
    echo "\n";

    $processedListsFromDB = array();
    $stmt = $dbh->prepare("SELECT url FROM processed");
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $processedListsFromDB[] = $row['url'];
        }
    }

    $storedLinksFromDB = array();
    $stmt = $dbh->prepare("SELECT url FROM links");
    if ($stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $storedLinksFromDB[] = $row['url'];
        }
    }

    $queue = array();
    $processedLists = $processedListsFromDB;
    $storedLinks = $storedLinksFromDB;

    // if script halted previously, make base from last inserted row in processed table
    if (!$mode) {
        // TODO redefine base
        $stmt = $dbh->prepare("SELECT url FROM processed where id =  (select max(id) from processed) ");
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $base .= $row['url'];
            }
        }

    }

    $allPageLinks = getPageLinks($base);
    $sanitizedUrls = sanitizeUrl($allPageLinks, $siteIdentifier);
    queueProcessor($sanitizedUrls, $queue, $processedLists);
    storeLinks($storedLinks, $sanitizedUrls);

    while (!empty($queue)) {
        $relativePath = array_shift($queue);

        if (in_array($relativePath, $processedLists)) {
            continue;
        }

        $newBase = $base. $relativePath;

        echo "Now Visiting: $newBase";
        echo "\n";

        $allPageLinks = getPageLinks($newBase);
        $sanitizedUrls = sanitizeUrl($allPageLinks, $siteIdentifier);
        queueProcessor($sanitizedUrls, $queue, $processedLists, $relativePath);
        storeLinks($storedLinks, $sanitizedUrls);
      

        echo "QUEUE Size: ". count($queue);
        echo "\n";

        $stmt = $dbh->prepare("INSERT INTO processed (url) VALUES (:url)");
        $stmt->bindParam(':url', $url);

        //print_r("URL: ", $url);

        $url = $relativePath;
        $stmt->execute();

        array_push($processedLists, $relativePath);

        echo "links processed :". count($processedLists);
        echo "\n";
    }
}

/**
 * store valid links on database so that we know which links are present in site
 * @param $storedLinks
 * @param $sanitizedUrls
 */
function storeLinks(&$storedLinks, $sanitizedUrls) {
    global $dbh;

    $stmt = $dbh->prepare("INSERT INTO links (url) VALUES (:url)");
    $stmt->bindParam(':url', $url);

    echo strftime("%c"). "in storeLinks";
    echo "\n";
    foreach ($sanitizedUrls as $link) {
        if (!in_array($link, $storedLinks)) {
            // TODO store the link

            $url = $link;
            $stmt->execute();

            array_push($storedLinks, $link);
        }
    }
}

/**
 * builds a queue to made recursive search for links on pages
 * @param $sanitizedUrls
 * @param $queue
 * @param $processedLists
 */
function queueProcessor($sanitizedUrls, &$queue, &$processedLists, $currentLink='') {
    echo strftime("%c"). "in queueProcessor";
    echo "\n";
    // we need to differentiate between queue's empty state and first time initialization
    static $queueExists = 0;

    if (!$queueExists) {
        // initialize queue
        if (empty($queue)) {
            $queue = $sanitizedUrls;
        }
        $queueExists = 1;
    } else {
            foreach ($sanitizedUrls as $link) {
                if (!in_array($link, $processedLists) && !in_array($link, $queue) && $link!=$currentLink) {
                    array_push($queue, $link);
                }
            }
    }
}

/**
 *  it takes a page link and returns all  links on that page
 * @param string $site from which url to grab links
 * @return array of links on a paga
 */
function getPageLinks($site='') {
    echo strftime("%c"). "in getPageLinks";
    echo "\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $site);	// The url to get links from
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	// We want to get the respone
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);

    if ($result === FALSE) {

        echo "cURL Error: " . curl_error($ch);
        echo "\n";
        //print_r($info);
    }

    curl_close($ch);


    $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";

    $linksToProcess = array();
    if(preg_match_all("/$regexp/siU", $result, $matches)) {
        $linksToProcess = $matches[2];
    }

    return $linksToProcess;
}

/**
 *  returns all the relative url's of the page
 * @param $linksToProcess
 * @param $siteIdentifier
 * @return array
 */
function sanitizeUrl($linksToProcess, $siteIdentifier) {
    echo strftime("%c"). "in sanitizeUrl";
    echo "\n";

    $relativeUrls = array();

    foreach ($linksToProcess as $link) {

        if (!empty($link)) {
            // links have any http or www?
            if (stristr($link, 'www.') || preg_match('/^http/', $link)) {
                // if site identifier not present then not internal link
                if (!stristr($link, $siteIdentifier)) {
                    //echo "external link";
                    // do nothing with this url, move to next url
                    continue;
                } else {
                    $urlParts = parse_url($link);
                    array_push($relativeUrls, $urlParts['path']);
                    continue;
                }
            }
            // TODO add some validation here
            // discard query strings
            $link = preg_replace('/\?.*/', '', $link);
            array_push($relativeUrls, $link);
        }
    }

    $relativeUrls = array_unique($relativeUrls);

    // TODO add patterns for not valid url
    $fobiddenPatterns = array('javascript:', '^\/$', '^#');
    $keys = array();

    foreach ($relativeUrls as $key => $value) {
        foreach ($fobiddenPatterns as $pattern) {
            if (preg_match("/$pattern/i", $value)) {
                $keys[] = $key;
            }
        }
    }

    foreach ($keys as $key) {
        unset($relativeUrls[$key]);
    }

    // add openling slash in case any url missing that
    foreach ($relativeUrls as &$link) {
        if (!preg_match("#^/#", $link)) {
            $link = '/'.$link;
        }
    }


    return $relativeUrls;
}

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);
echo "This page was created in ".$totaltime." seconds";


