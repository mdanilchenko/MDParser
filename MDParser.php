<?php

/**
 * User: maksimdanilchenko
 * Date: 25.05.16
 * Time: 20:30
 */
class MDParser
{
    private $templateHtmlFile = 'template/template.html';
    private $lineTemplates = array('/^#####(.*)$/m', '/^####(.*)$/m', '/^###(.*)$/m', '/^##(.*)$/m', '/^#(.*)$/m','/^u#####(.*)$/m', '/^u####(.*)$/m', '/^u###(.*)$/m', '/^u##(.*)$/m', '/^u#(.*)$/m','/^a#####(.*)$/m', '/^a####(.*)$/m', '/^a###(.*)$/m', '/^a##(.*)$/m', '/^a#(.*)$/m', '/^(\-+)$/m', '/^(_+)$/m');
    private $lineReplacements = array('<h5>$1</h5>', '<h4>$1</h4>', '<h3>$1</h3>', '<h2>$1</h2>', '<h1>$1</h1>','<h5 class="updated">$1</h5>', '<h4 class="updated">$1</h4>', '<h3 class="updated">$1</h3>', '<h2 class="updated">$1</h2>', '<h1 class="updated">$1</h1>','<h5 class="added">$1</h5>', '<h4 class="added">$1</h4>', '<h3 class="added">$1</h3>', '<h2 class="added">$1</h2>', '<h1 class="added">$1</h1>', '<hr />', '<hr />');

    private $startEndTemplates = array( '/``(.+)``/', '/\*\*(.*)\*\*/', '/\*(.*)\*/','/\[@(.+)\]/','/\t/');
    private $startEndReplacements = array( '<span class="code">$1</span>', '<b>$1</b>', '<i>$1</i>', '<a name="$1"></a>','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');

    private $listTemplates = array('/^(\d+)\.(.*)$/m', '/^\*(.*)$/m', '/\[(.+)\]\((.+)\)/i');
    private $listReplacement = array('<div><span class="list_num">$1</span> $2</div>', '<div><span class="list_uns">&bull;</span> $1</div>', '<a target="_blank" href="$2" >$1</a>');

    public function parseToHtml($text)
    {
        $title = "Document";
        preg_match_all('/^#(.*?)$/m', $text, $matches);
        if(isset($matches[1]) and (count($matches[1]>0))){
            $title = $matches[1][0];
        }
        $text = preg_replace($this->lineTemplates, $this->lineReplacements, $text);
        preg_match_all('/```(.+)```/Us', $text, $matches);
        if(isset($matches[0]) and (count($matches[0])>0)){
            for($i=0;$i<count($matches[0]);$i++) {
                $replaced = str_replace("\n","<br>",trim($matches[1][$i],"\n"));
                $text = str_replace($matches[0][$i],'<div class="code">'.$replaced.'</div>',$text);
            }
        }

        $text = preg_replace($this->startEndTemplates, $this->startEndReplacements, $text);
        $text = preg_replace($this->listTemplates, $this->listReplacement, $text);

        $text = preg_replace('/(\r\n|\n){2}/',"<br />" ,$text );

        $html = file_get_contents($this->templateHtmlFile);
        $html = str_replace('%title%',$title ,$html );
        $html = str_replace('%body%',$text ,$html );

        return $html;
    }

    private function getDeepHashes($text,$matchLevel=2){
        preg_match_all('/^#{'.$matchLevel.'}(.*?)$/m', $text, $matches);
        $result_hashes = array();
        if(isset($matches[0]) and (count($matches[0])>0)){
            $splits = preg_split('/^#{'.$matchLevel.'}(.*?)$/m',$text);
            for($i = 1;$i<count($splits);$i++){
                $result_hashes[$matches[0][$i-1]] =md5($splits[$i]);
            }
        }
        return $result_hashes;
    }
    public function findUpdates($olderText,$newerText,$matchLevel=2){
        $oldHashes = $this->getDeepHashes($olderText,$matchLevel);
        $newHashes = $this->getDeepHashes($newerText,$matchLevel);

        $results = array('updated'=>array(),'deleted'=>array(),'added'=>array());
        foreach($newHashes as $key=>$md5){
            if(!isset($oldHashes[$key])){
                $results['added'][] = $key;
            }else if($oldHashes[$key]!=$md5){
                $results['updated'][] = $key;
            }
        }
        foreach($oldHashes as $key=>$md5){
            if(!isset($newHashes[$key])){
                $results['deleted'][] = $key;
            }
        }
        return $results;
    }
    public function parseVersionCompare($prevText,$newText,$matchLevel=2){
        $updates = $this->findUpdates($prevText,$newText,$matchLevel );
        if(count($updates['added'])>0){
            for($i = 0;$i<count($updates['added']);$i++){
                $newText = str_replace($updates['added'][$i],'a'.$updates['added'][$i] ,$newText);
            }
        }
        if(count($updates['updated'])>0){
            for($i = 0;$i<count($updates['updated']);$i++){
                $newText = str_replace($updates['updated'][$i],'u'.$updates['updated'][$i] ,$newText);
            }
        }
        return $this->parseToHtml($newText);
    }

}

?>