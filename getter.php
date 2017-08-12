<?php
#url = http://feeds.feedburner.com/tedtalkshd
$url = "http://feeds.feedburner.com/tedtalkshd";
$data = file_get_contents($url);
$outname = date('YmdHis') . '.html';
file_put_contents($outname,$data);

#$outname = '20170812204857.html';

$files1 = scandir('.');
$max_download_per_run = 10;
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
        $outmp4 = sprintf("TED-S01E%03d-%s.mp4",$next_epnum,$last_one);       
	$outjpg = sprintf("TED-S01E%03d-%s.jpg",$next_epnum,$last_one);
        echo "url = $url ($size bytes) - $outmp4\n";
	# we dont directly ask wget to write on target file as 
	# incomplete downoloads would also b marked complete as filename would match
	system("wget --continue -O temp.mp4 $url");
	#rename('temp.mp4',$outmp4);
        #$mp4 = file_get_contents($url);
        #file_put_contents($outmp4,$mp4);
        file_put_contents("next_epnum.txt",++$next_epnum);
	system("AtomicParsley temp.mp4 --output $outmp4 --title \"$title\"");
	touch($outmp4,strtotime($pubDate));
	system("wget --continue -O $outjpg $thumbnail");
        echo "Wrote $outmp4.\n";
	unlink("temp.mp4");
        if(!--$max_download_per_run)
        {
            echo "Enough for today.\n";
            break;
        }
    }
}

