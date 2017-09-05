<?php
 
// Take credit for your work.
$wgExtensionCredits['parserhook'][] = array(
 
   // The full path and filename of the file. This allows MediaWiki
   // to display the Subversion revision number on Special:Version.
   'path' => __FILE__,
 
   // The name of the extension, which will appear on Special:Version.
   'name' => 'Step Numbering Function',
 
   // A description of the extension, which will appear on Special:Version.
   'description' => 'A simple function extension to number steps',
 
   // Alternatively, you can specify a message key for the description.
   'descriptionmsg' => 'stepnumber-desc',
 
   // The version of the extension, which will appear on Special:Version.
   // This can be a number or a string.
   'version' => 1, 
 
   // Your name, which will appear on Special:Version.
   'author' => 'Pierre Boutet',
 
   // The URL to a wiki page/web page with information about the extension,
   // which will appear on Special:Version.
   //'url' => 'https://www.mediawiki.org/wiki/Manual:Parser_functions',
 
);
 
// Specify the function that will initialize the parser function.
$wgHooks['ParserFirstCallInit'][] = 'StepNumberSetupParserFunction';
 
// Allow translation of the parser function name
$wgExtensionMessagesFiles['StepNumber'] = __DIR__ . '/StepNumber.i18n.php';
 
// Tell MediaWiki that the parser function exists.
function StepNumberSetupParserFunction( &$parser ) {
 
   // Create a function hook associating the "example" magic word with the
   // ExampleExtensionRenderParserFunction() function. See: the section 
   // 'setFunctionHook' below for details.
   $parser->setFunctionHook( 'stepNumber', 'StepNumberRenderParserFunction' );
   $parser->setFunctionHook( 'idGenerator', 'IdGeneratorRenderParserFunction' );
 
   // Return true so that MediaWiki continues to load extensions.
   return true;
}
 
// Render the output of the parser function.
function StepNumberRenderParserFunction( $parser, $param1 = 'default' , $param2 = '' ) {
 
   // The input parameters are wikitext with templates expanded.
   // The output should be wikitext too.
   static $counters = array();
   if ($param1 == 'init') {
      $param2 = 'init';
      $param1 = 'default';
   }
   if ( ! isset($counters[$param1])) {
      $counters[$param1] = 0;
   }
   if ($param2 == 'init') {
      $counters[$param1] = 0;
      return '';
   } else {
      $counters[$param1] ++;
      return $counters[$param1];
   }
}
 
// Render the output of the parser function.
function IdGeneratorRenderParserFunction( $parser, $param1 = 'default' , $param2 = '' ) {
 
   // The input parameters are wikitext with templates expanded.
   // The output should be wikitext too.
   static $counters = array();
   static $nextCounterId = -1;
   if($nextCounterId  <0) {
      $nextCounterId  = rand(1,999999);
   }
   if ($param1 == 'init') {
      $param2 = 'init';
      $param1 = 'default';
   }
   if ($param2 == 'init') {
      $counters[$param1] = $nextCounterId;
      $nextCounterId++;
      return 'init '. $param1;
   } else {
      if ( ! isset($counters[$param1])) {
         $counters[$param1] = $nextCounterId;
         $nextCounterId++;
      }
      return $counters[$param1];
   }
}