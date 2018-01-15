<?php

class search_it_autocomplete{
  
  function __construct(){}
  
  
  /**
   * A function for retrieving the K?lner Phonetik value of a string
   *
   * As described at http://de.wikipedia.org/wiki/K?lner_Phonetik
   * Based on Hans Joachim Postel: Die K?lner Phonetik.
   * Ein Verfahren zur Identifizierung von Personennamen auf der
   * Grundlage der Gestaltanalyse.
   * in: IBM-Nachrichten, 19. Jahrgang, 1969, S. 925-931
   *
   * This program is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   *
   * @package phonetics
   * @version 1.0
   * @link http://www.einfachmarke.de
   * @license GPL 3.0 <http://www.gnu.org/licenses/>
   * @copyright  2008 by einfachmarke.de
   * @author Nicolas Zimmer <nicolas dot zimmer at einfachmarke.de>
   */
  
  function cologne_phone($_word) {
    
    /**
     * @param  string  $_word string to be analyzed
     * @return string  $value represents the K?lner Phonetik value
     * @access public
     */
    
    //prepare for processing
    $_word = strtolower($_word);
    $substitution = array(
      '?'=>'a',
      '?'=>'o',
      '?'=>'u',
      '?'=>'ss',
      'ph'=>'f'
    );
    
    foreach($substitution as $letter => $substitution)
      $_word = str_replace($letter, $substitution, $_word);
      
      $len = strlen($_word);
      
      //Rule for exeptions
      $exceptionsLeading = array(
        4=>array('ca','ch','ck','cl','co','cq','cu','cx'),
        8=>array('dc','ds','dz','tc','ts','tz')
      );
      
      $exceptionsFollowing = array('sc','zc','cx','kx','qx');
      
      //Table for coding
      $codingTable = array(
        0 => array('a','e','i','j','o','u','y'),
        1 => array('b','p'),
        2 => array('d','t'),
        3 => array('f','v','w'),
        4 => array('c','g','k','q'),
        48 => array('x'),
        5 => array('l'),
        6 => array('m','n'),
        7 => array('r'),
        8 => array('c','s','z')
      );
      
      for($i=0; $i < $len; $i++)
      {
        $value[$i] = '';
        
        //Exceptions
        if($i==0 AND $len > 1 AND $_word[$i].$_word[$i+1] == 'cr')
          $value[$i] = 4;
          
          if($i < ($len - 1))
          {
            foreach($exceptionsLeading as $code=>$letters)
            {
              if(in_array($_word[$i].$_word[$i+1],$letters))
                $value[$i] = $code;
            }
          }
          
          if($i AND in_array($_word[$i-1].$_word[$i], $exceptionsFollowing))
            $value[$i] = 8;
            
            //Normal encoding
            if($value[$i] == '')
            {
              foreach($codingTable as $code => $letters)
              {
                if(in_array($_word[$i], $letters))
                  $value[$i] = $code;
              }
            }
      }
      
      //delete double values
      $len=count($value);
      
      for($i=1;$i<$len;$i++)
      {
        if($value[$i] == $value[$i-1])
          $value[$i] = '';
      }
      
      //delete vocals
      for ($i=1;$i>$len;$i++)
      {
        //omitting first characer code and h
        if($value[$i] == 0)
          $value[$i] = '';
      }
      
      
      $value = array_filter($value);
      $value = implode('', $value);
      
      return $value;
  }

  
}


?>