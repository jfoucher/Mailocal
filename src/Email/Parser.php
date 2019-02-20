<?php
/**
 * Parser.php
 *
 * Created By: jonathan
 * Date: 20/02/2019
 * Time: 13:45
 */
namespace App\Email;

use MS\Email\Parser\Parser as BaseParser;
use Html2Text\Html2Text;

class Parser extends BaseParser
{
    protected function getHtml(){
        if(!is_array($this->parts['body'])){
            if (preg_match('/(\<html|\<body)/', $this->parts['body'])) {
                return quoted_printable_decode($this->parts['body']);
            }
            return false;
        }
        if($r = $this->searchByHeader('/content\-type/','/text\/html/')) {
            return $r[0]->getDecodedContent();
        }

        return false;
    }

    protected function getText(){
        if(!is_array($this->parts['body'])){
            if (preg_match('/(\<html|\<body)/', $this->parts['body'])) {
                $txt = new Html2Text(quoted_printable_decode($this->parts['body']));
                return preg_replace('/^[\s]+/m', '',
                    trim($txt->getText(),  " \t\n\r ".urldecode("%C2%A0")));
            }
            return $this->parts['body'];
        }
        if($r = $this->searchByHeader('/content\-type/','/text\/plain/')) {
            return $r[0]->getDecodedContent();
        }

        return false;
    }
}