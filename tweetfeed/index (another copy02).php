<?php
#This script was not commented.  Neil is adding comments with his limited ability to enterpret PHP. anything on a line  after a # outside of '' does nothing but explain what Neil thinks it does
mb_http_output('UTF-8'); 

$feedXML = file_get_contents("https://www.nepalmonitor.org/index.php/feed"); # Fills a variable $FeedXML with the contents of the raw feed
$categoryHashtags = json_decode(file_get_contents("category-hashtags.json"), true); # Fills $categoryHashags with the contents of the category replacement file

$xml=simplexml_load_string($feedXML) or die("There's an error in our Twitter feed system. We're working on it. Until then you can view new reports directly at nepalmonitor.org"); # If there is an error display this message

$arr = array(' '); #$arr is now an array, which is basically a list of values that can be recalled via a position defined by a number, it will be filled with hashtag strings below

foreach ($xml->xpath('//item') as $item) { # For every item in the orginal feed do the process below
    $r = '...#Nepal'; #  Create a new string, $r, with the the Nepal hashtag in the begining
    foreach($item->children() as $category){ # Go through each "child" tag of a given item in the orginal feed and take the follwing steps
        if($category->getName() == 'category'){  # IF the tag is a category... 
            $categoryString = (string)$category; # Then $categoryString = what that category's name is 
            if($categoryHashtags[$categoryString]){ # Look in the category replacement file for a replacement hashtag, and IF it exists do the folowing 
            	if (strpos($categoryHashtags[$categoryString], '@') !== FALSE) # If the replacement tag contains an '@' (the search for it isn't false)
    			$h = ' '; # Then a space will be put infront of it.
		else
    			$h = ' #'; # Otherwise, a hashtag and a space will be put infront of it.
                if(strlen($r.$h.$categoryHashtags[$categoryString])<=38 and strlen($categoryHashtags[$categoryString])>=3) # If the current string + the new hashag are less then 38 characters long Added by Neil-> If the replacement category hastag is as long as 3 characters
                	
               	$r .= $h.$categoryHashtags[$categoryString]; #THEN add the new hash tag to the string 
            }else{ #If there is not a replacement hashtag found for the category name
                $t = $h.(preg_split('/ |\//', $categoryString)[0]); # Then make a new string called $t and put in the first word of the full category name after a hash tag. 
                if(strlen($r.$t)<=38) #As long as the current string + t above is under 38 characters 
                $r .= $t; #Then add the hashtag in $t to it. 
            } 
        }
    }
    array_push($arr, $r); # This is filling an array ($arr) with the strings created above. 
}

function str_replace_nth($search, $replace, $subject, $nth) # This is a creating a function to replace some items in the strings. Not sure what yet. 
{
    $found = preg_match_all('/'.$search.'/u', $subject, $matches, PREG_OFFSET_CAPTURE);
    if (false !== $found && $found > $nth) {
        return substr_replace($subject, $replace, $matches[0][$nth][1], strlen($search));
    }
    return $subject;
}
$feedXML=htmlspecialchars($feedXML); #This is probably formating special html characters for the feed. Probably keeps everything clean while doing the replace below.

$feedXML = preg_replace('/&lt;title&gt;(.{1,70})(.+)&lt;\/title&gt;/u', "&lt;title&gt;$1&lt;/title&gt;", $feedXML); # Replace the titles after the first 70 characters with the strings created above and stored in the array. The resulting title should be just under 108 charcters

foreach($arr as $m=>$s){
    $feedXML = str_replace_nth('&lt;\/title&gt;', $arr[$m].'&lt;/title&gt;', $feedXML, $m); #Process the feed with the function above. as long as $m is greater than $s. I don't know what $s is though
}

$report = fopen("feed.xml", w);
fwrite($report, htmlspecialchars_decode($feedXML)); #Overwrite feed.xml with our newly created feed while decoding special HTML characters
fclose($report);

header('Location: feed.xml');

?>