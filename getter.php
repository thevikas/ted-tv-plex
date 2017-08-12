<?php
#url = http://feeds.feedburner.com/tedtalkshd
$url = "http://feeds.feedburner.com/tedtalkshd";
#$data = file_get_contents($url);
#$outname = date('YmdHis') . '.html';
#file_put_contents($outname,$data);

$outname = '20170812204857.html';

$files1 = scandir('.');
print_r($files1);
$doc = new DOMDocument();
$doc->load($outname);
$xpath = new DOMXpath($doc);
$next_epnum = intval(file_get_contents('next_epnum.txt'));
$elements = $xpath->query('//item');
if (!is_null($elements)) {
    foreach ($elements as $element) {
        $size = $link = $url = $pubDate = $thumbnail = $title = "";

        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            switch($node->nodeName)
            {
            case 'title':
                $title = $node->nodeValue;
                break;
            case 'enclosure':
                $url = $node->getAttribute('url');
                $size = $node->getAttribute('length');
                break;
            case 'pubDate':
                $pubDate = $node->nodeValue;
                break; 
            case 'link':
                $link = $node->nodeValue;
                break;
            case 'media:thumbnail':
                $thumbnail = $node->getAttribute('url');
                break;
            }
            #check file exists using link
        }
        $ss = explode('/',$link);
        $last_one = $ss[count($ss) -1];
        $matches = array_filter($files1, function($var) use ($last_one) { return preg_match("/\b$last_one\b/i", $var); });
        if(count($matches))
        {
            echo "Ignoring [$last_one] as already downloaded\n";
            continue;
        }        
        echo "url = $url ($size bytes)\n";
        $mp4 = file_get_contents($url);
        $outmp4 = sprintf("TED-S01E%0d-%s.mp4",$next_epnum,$last_one);
        file_put_contents($outmp4,$mp4);
        file_put_contents("next_epnum.txt",++$next_epnum);
        echo "Wrote $outmp4.\n";
    }
}

