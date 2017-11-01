<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  ©2017, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\IO;


/**
 * This class defines a exception, thrown if a file already exists but it should not exist.
 *
 * It extends from {@see \Niirrty\IO\IOException}.
 *
 * @since v0.1
 */
class FileAlreadyExistsException extends IOException
{


   # <editor-fold desc="= = =   C O N S T R U C T O R   +   D E S T R U C T O R   = = = = = = = = = = = = = = =">

   /**
    * Init's a new instance.
    *
    * @param string     $file     The already existing file
    * @param string     $message  The optional error message
    * @param integer    $code     The optional error code (Default to \E_USER_ERROR)
    * @param \Throwable $previous A optional previous exception
    */
   public function __construct(
      string $file, string $message = null, int $code = \E_USER_ERROR, \Throwable $previous = null )
   {

      parent::__construct(
         $file,
         'The File does already exist.' . static::appendMessage( $message ),
         $code,
         $previous
      );

   }

   # </editor-fold>


}

